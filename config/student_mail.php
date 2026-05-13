<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Envio en cola (futuro)
    |--------------------------------------------------------------------------
    |
    | Cuando se active, despachar App\Jobs\SendRegistrationConfirmationJob
    | en lugar de la llamada sincrona desde StudentMailService.
    |
    */
    'queue_enabled' => env('STUDENT_REGISTRATION_MAIL_QUEUE', false),

    /** Nombres de adjuntos en el correo del postulante. */
    'attachment_enrollment_filename' => 'ficha-inscripcion.pdf',

    'attachment_regulations_filename' => 'reglamento-institucional.pdf',

];
