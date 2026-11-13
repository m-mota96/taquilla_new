<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tipos de evento
    |--------------------------------------------------------------------------
    |
    | Se usan en event_settings.event_type y positions.event_type para saber
    | que cargos se muestran en el formulario de cada evento.
    |
    */

    'event_types' => [
        1 => 'WF',
        2 => 'COMVET',
    ],

    /*
    |--------------------------------------------------------------------------
    | API de conferencias
    |--------------------------------------------------------------------------
    |
    | En el modo "un registro por persona" cada asistente se envia a esta API.
    |
    */

    'conferences' => [
        'api_url'  => env('CONFERENCES_API_URL', 'https://conferences.api.maxwellcorp.mx/api/tickets/create'),
        'url_base' => env('CONFERENCES_URL_BASE', 'https://taquilla.ticketland.mx'),
        'password' => env('CONFERENCES_DEFAULT_PASSWORD', '123456789'),
    ],

];
