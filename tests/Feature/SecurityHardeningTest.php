<?php

test('respuestas web incluyen headers de seguridad', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
});

test('login administrativo bloquea intentos repetidos por ip y usuario', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'username' => 'admin',
            'password' => 'clave-incorrecta',
        ])->assertSessionHasErrors('username');
    }

    $this->post(route('login'), [
        'username' => 'admin',
        'password' => 'clave-incorrecta',
    ])->assertStatus(429);
});
