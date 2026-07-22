<?php

test('the application root redirects to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('the login page is accessible', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});
