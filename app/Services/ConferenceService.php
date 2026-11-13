<?php

namespace App\Services;

use App\Models\OrderAttendee;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envia los asistentes del modo "un registro por persona" a la plataforma de conferencias.
 *
 * Una falla aqui nunca debe deshacer la venta: el asistente queda con
 * conference_sent_at en null y el organizador puede reenviarlo desde Configuraciones.
 */
class ConferenceService
{
    /**
     * Envia varios asistentes. Si la API no responde (error de conexion) se detiene
     * y deja el resto pendiente, para no acumular un timeout por cada asistente.
     *
     * @param  iterable<OrderAttendee>  $attendees
     * @return array{sent:int, failed:int}
     */
    public function sendMany($attendees)
    {
        $sent = 0;
        $failed = 0;
        $stop = false;

        foreach ($attendees as $attendee) {
            if ($stop) {
                $failed++;
                continue;
            }

            try {
                $this->send($attendee) ? $sent++ : $failed++;
            } catch (ConnectionException $e) {
                Log::error('Conferencias: no se pudo conectar con la API. '.$e->getMessage());
                $failed++;
                $stop = true;
            } catch (\Throwable $e) {
                // La venta ya está guardada: cualquier otro error aquí no debe mostrarse como venta fallida
                // (la cajera la repetiría y se cobraría doble). Ojo: si falló al guardar la respuesta,
                // el ticket pudo crearse en conferencias aunque aquí quede pendiente.
                Log::error('Conferencias: error inesperado con el asistente '.$attendee->id.'. '.$e->getMessage());
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * @throws ConnectionException  si la API no responde
     */
    public function send(OrderAttendee $attendee)
    {
        $attendee->loadMissing(['order.event', 'ticket', 'position']);
        $event = $attendee->order->event;

        if (empty($event->uuid_events_conferences)) {
            Log::warning('Conferencias: el evento '.$event->id.' ('.$event->name.') no tiene uuid_events_conferences; asistente '.$attendee->id.' no enviado.');
            return false;
        }

        $dataConferences = [
            'event_id' => $event->uuid_events_conferences,
            'code'     => $attendee->qr,
            'name'     => $attendee->name,
            'email'    => $attendee->email,
            'password' => config('taquilla.conferences.password'),
            'phone'    => $attendee->country_code.$attendee->phone, // ej. +525512345678
            'metadata' => [
                'event'      => $event->name,
                'ticket'     => $attendee->ticket->name,
                'url_base'   => config('taquilla.conferences.url_base'),
                'table'      => 'order_attendees',
                'id_table'   => $attendee->id,
                'occupation' => mb_strtoupper($attendee->position->name),
            ],
        ];

        $response = Http::acceptJson()
            ->connectTimeout(5)
            ->timeout(10)
            ->post(config('taquilla.conferences.api_url'), $dataConferences);

        if ($response->failed()) {
            Log::error('Conferencias: error al registrar asistente '.$attendee->id.' (HTTP '.$response->status().'): '.$response->body());
            Log::error('Conferencias: datos enviados: '.json_encode($dataConferences, JSON_UNESCAPED_UNICODE));
            return false;
        }

        // Conferencias devuelve el id del ticket (data.ticket_id); es el valor del QR del gafete.
        $ticketId = $response->json('data.ticket_id');
        if (empty($ticketId)) {
            Log::warning('Conferencias: la API no devolvió data.ticket_id para el asistente '.$attendee->id.'; no se podrá imprimir su gafete. Respuesta: '.$response->body());
        }

        $attendee->conference_ticket_id = $ticketId ?: null;
        $attendee->conference_sent_at = now();
        $attendee->save();

        return true;
    }
}
