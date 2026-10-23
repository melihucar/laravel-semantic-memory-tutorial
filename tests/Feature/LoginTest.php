<?php

use App\Models\User;

it('sends guests to the login page', function () {
    $this->get('/')->assertRedirect('/login');
    $this->postJson('/chat', ['message' => 'Hi'])->assertUnauthorized();
});

it('logs a user in with the right password', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs a user out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
