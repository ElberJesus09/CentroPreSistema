<?php

namespace App\Services;

/**
 * Resultado de intento de envio SMTP (mensaje al usuario en español).
 */
final class StudentMailSendOutcome
{
    private function __construct(
        public readonly bool $sent,
        public readonly ?string $userMessage = null,
    ) {}

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $userMessage): self
    {
        return new self(false, $userMessage);
    }
}
