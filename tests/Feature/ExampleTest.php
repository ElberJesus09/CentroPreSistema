<?php

test('home page is reachable for guests', function () {
    $response = $this->get('/');

    $response->assertOk();
});

test('admin login page is reachable for guests', function () {
    $response = $this->get('/admin/login');

    $response->assertOk();
});
