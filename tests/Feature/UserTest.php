<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRegisterUserSuccess()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
        ])->json('POST', '/api/register', ['name' => 'Manuel',
            'username' => 'Manuel', 'email'=>'email@email.com', 'password' => 'Manuel123',
            'confirm_password' => 'Manuel123']);
        $response->assertStatus(201);
    }
}
