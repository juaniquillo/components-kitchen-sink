<?php

declare(strict_types=1);

use App\Emails\Exceptions\MissingEmailUserException;
use App\Models\User;

test('unknown email returns 404', function () {
    $this->get('/emails/bogus')->assertNotFound();
});

test('known email without users throws missing user exception', function () {
    $this->withoutExceptionHandling();

    $this->expectException(MissingEmailUserException::class);
    $this->expectExceptionMessage('welcome');

    $this->get('/emails/welcome');
});

test('known email with a user renders successfully', function () {
    User::factory()->create(['name' => 'Ada']);

    $this->get('/emails/welcome')->assertOk();
});
