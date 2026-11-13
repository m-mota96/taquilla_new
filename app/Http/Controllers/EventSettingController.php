<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSetting;
use App\Models\OrderAttendee;
use App\Models\Position;
use App\Models\User;
use App\Services\ConferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Auth;

class EventSettingController extends Controller
{
    /**
     * Cuantos asistentes se reenvian por clic. Evita que la peticion
     * exceda el tiempo maximo si hay muchos pendientes.
     */
    const RESEND_BATCH = 20;

    public function edit($event_id) {
        $event = $this->ownEvent($event_id);
        if (empty($event)) {
            return redirect('home');
        }

        $user = User::where('id', Auth::user()->id)->first();
        $positions = Position::orderBy('event_type')->orderBy('id')->get()->groupBy('event_type');
        $pending = OrderAttendee::pendingConference($event->id)->count();

        return view('customers.settings')->with([
            'event'      => $event,
            'user'       => $user,
            'setting'    => $event->setting,
            'eventTypes' => config('taquilla.event_types'),
            'positions'  => $positions,
            'pending'    => $pending,
        ]);
    }

    public function update(Request $request, $event_id) {
        $event = $this->ownEvent($event_id);
        if (empty($event)) {
            return response()->json(['error' => true, 'msj' => 'Evento no encontrado'], 202);
        }

        $perPerson = EventSetting::SALE_MODE_PER_PERSON;
        $validator = Validator::make($request->all(), [
            'sale_mode'               => ['required', Rule::in([EventSetting::SALE_MODE_PER_PURCHASE, $perPerson])],
            'event_type'              => ['nullable', "required_if:sale_mode,$perPerson", Rule::in(array_keys(config('taquilla.event_types')))],
            'uuid_events_conferences' => ['nullable', "required_if:sale_mode,$perPerson", 'string', 'max:100'],
        ], [
            'sale_mode.required'               => 'Seleccione el modo de venta',
            'sale_mode.in'                     => 'El modo de venta no es válido',
            'event_type.required_if'           => 'Seleccione el tipo de evento (se usa para los cargos)',
            'event_type.in'                    => 'El tipo de evento no es válido',
            'uuid_events_conferences.required_if' => 'Capture el UUID del evento en conferencias',
            'uuid_events_conferences.max'      => 'El UUID no puede tener más de 100 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true, 'msj' => $validator->errors()->first()], 202);
        }

        EventSetting::updateOrCreate(
            ['event_id' => $event->id],
            ['sale_mode' => $request->sale_mode, 'event_type' => $request->event_type ?: null]
        );

        $event->uuid_events_conferences = trim((string) $request->uuid_events_conferences) ?: null;
        $event->save();

        return response()->json(['error' => false, 'msj' => 'La configuración se guardó correctamente'], 202);
    }

    public function resendConferences($event_id, ConferenceService $conferences) {
        $event = $this->ownEvent($event_id);
        if (empty($event)) {
            return response()->json(['error' => true, 'msj' => 'Evento no encontrado'], 202);
        }

        if (empty($event->uuid_events_conferences)) {
            return response()->json([
                'error' => true,
                'msj'   => 'Primero capture y guarde el UUID del evento en conferencias'
            ], 202);
        }

        $attendees = OrderAttendee::pendingConference($event->id)->orderBy('id')->limit(self::RESEND_BATCH)->get();
        $result = $conferences->sendMany($attendees);
        $pending = OrderAttendee::pendingConference($event->id)->count();

        return response()->json([
            'error'   => false,
            'sent'    => $result['sent'],
            'failed'  => $result['failed'],
            'pending' => $pending,
        ], 202);
    }

    /**
     * Solo el organizador dueño del evento puede verlo o modificarlo.
     */
    private function ownEvent($event_id) {
        return Event::with('setting')->where('id', $event_id)->where('user_id', Auth::user()->id)->first();
    }
}
