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
    | Logos de patrocinadores del gafete (por tipo de evento)
    |--------------------------------------------------------------------------
    |
    | "Cargar logos" manda a la impresora la imagen del tipo de evento.
    | script: archivo en public/  |  var: variable global que define ese archivo.
    | Para cambiar una imagen se regenera su .js (PNG de 608 px de ancho).
    |
    */

    'badge_logos' => [
        1 => ['script' => 'js/patrocinadores-wf-zpl.js',     'var' => 'patrocinadoresWfZPL'],
        2 => ['script' => 'js/patrocinadores-comvet-zpl.js', 'var' => 'patrocinadoresComvetZPL'],
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
