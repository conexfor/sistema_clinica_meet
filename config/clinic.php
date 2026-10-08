<?php

/*
|--------------------------------------------------------------------------
| Datos públicos de la clínica
|--------------------------------------------------------------------------
| Información de contacto que se muestra en la landing pública.
| Cualquier valor puede sobrescribirse desde el .env.
*/

return [
    'name' => env('CLINIC_NAME', 'Conexfor'),
    'tagline' => env('CLINIC_TAGLINE', 'Psicología privada'),
    'city' => env('CLINIC_CITY', 'Ciudad'),

    'phone' => env('CLINIC_PHONE', '+00 000 000 000'),
    // Número en formato internacional sin "+" ni espacios, para wa.me
    'whatsapp' => env('CLINIC_WHATSAPP', '000000000000'),
    'email' => env('CLINIC_EMAIL', 'contacto@conexfor.com'),

    'address' => env('CLINIC_ADDRESS', 'Av. Principal 123'),
    'address_detail' => env('CLINIC_ADDRESS_DETAIL', 'Distrito Centro · Ciudad'),
    'hours' => env('CLINIC_HOURS', 'Lunes a sábado · 8:00 – 20:00'),

    'instagram' => env('CLINIC_INSTAGRAM', '#'),
    'linkedin' => env('CLINIC_LINKEDIN', '#'),
];
