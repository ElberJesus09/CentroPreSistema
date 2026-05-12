<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seguridad perimetral (preparacion futura)
    |--------------------------------------------------------------------------
    |
    | Hooks de configuracion para restriccion por IP, redes institucionales,
    | auditoria de accesos y logs de sesiones. No activar logica aun;
    | leer estos valores desde middleware/servicios cuando se implemente.
    |
    */
    'security' => [
        'ip_allowlist_enabled' => false,
        'ip_allowlist' => [],
        'network_audit_enabled' => false,
        'session_audit_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Infraestructura futura (cache distribuido, colas, API)
    |--------------------------------------------------------------------------
    */
    'scale' => [
        'preferred_cache_store' => env('INSTITUTION_CACHE_STORE', 'database'),
        'queue_connection_hint' => env('INSTITUTION_QUEUE_CONNECTION', 'database'),
    ],
];
