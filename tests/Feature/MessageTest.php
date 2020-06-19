<?php

namespace Tests\Feature;

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;

class MessageTest extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('oauth_clients')) {
            resolve(ClientRepository::class)->createPersonalAccessClient(
                null, config('app.name') . ' Personal Access Client', config('app.url')
            );
        }
    }

    public function testCreateUserMessageSuccess200()
    {
        $this->withExceptionHandling();
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Dominic Manuel",
                "username" => "Dominic",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json('POST',"/api/users/{$user1['success']['id']}/messages", ["message" => "test message",],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertCreated();
        $response->assertJson([
            "success" => "message sent to {$user1['success']['username']}",
        ]);
    }

    public function testCreateUserCommentSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json('POST',"/api/users/1/messages", ["message" => "test message", "status" => "comment", "comment_on"=> 1],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertCreated();
        $response->assertJson([
            "success" => "message sent to {$user['success']['username']}",
        ]);
    }


    public function testCreateUserMessageFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/users/1/messages');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    public function testGetUserMessageSuccess200()
    {
        $this->withExceptionHandling();
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user2 = $this->json('POST', "/api/register",
            ["name" => "Dominic Manuel",
                "username" => "Dominic",
                "email" => "example2@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $message = $this->json('POST',"/api/users/{$user1['success']['id']}/messages", ["message" => "test message"],
            ["Authorization" => "Bearer {$user2['success']['access_token']}", "Content-Type" => "application/json"]);
        $this->json('POST',"/api/users/{$user2['success']['id']}/messages",
            ["message" => "test message", "status" => "comment", "comment_on"=> $message['message']['id']],
            ["Authorization" => "Bearer {$user1['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->get("/api/users/{$user1['success']['id']}/messages",
            ["Authorization" => "Bearer {$user2['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson([
            ["username" => "{$user2['success']['username']}"]
        ]);
    }

    public function testGetUserMessageFailure404()
    {
        $this->withExceptionHandling();
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Dominic Manuel",
                "username" => "Dominic",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/users/{$user1['success']['id']}/messages",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'message' => 'No messages'
        ]);
    }


    public function testUpdateMessageSuccess200()
    {
        $this->withExceptionHandling();
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Dominic",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $message = $this->json('POST',"/api/users/{$user1['success']['id']}/messages", ["message" => "test message", "status" => "comment", "comment_on"=> 1],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json("PATCH", "/api/messages/{$message['message']['id']}", [
            "message" => "test update message"
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(["success" => "message updated successfully"]);
    }

    public function testUpdateMessageFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('PATCH', 'api/messages/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    public function testUpdateMessageFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("PATCH", "/api/messages/1", [
            "message" => "test update message"
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'message' => 'Message not found'
        ]);
    }

    public function testDeleteMessageSuccess200()
    {
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Dominic",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $message = $this->json('POST',"/api/users/{$user1['success']['id']}/messages", ["message" => "test message", "status" => "comment", "comment_on"=> 1],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json("PUT", "/api/messages/{$message['message']['id']}", [], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(["success" => "Message deleted successfully"]);
    }


    public function testDeleteMessageFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('PUT', 'api/messages/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    public function testDeleteMessageFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("PUT", "/api/messages/1", [
            "message" => "test update message"
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'message' => 'Message not found'
        ]);
    }

    public function testDestroyMessageSuccess200()
    {
        $user1 = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Dominic",
                "email" => "example1@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $message = $this->json('POST',"/api/users/{$user1['success']['id']}/messages", ["message" => "test message", "status" => "comment", "comment_on"=> 1],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/messages/{$message['message']['id']}", [], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertStatus(202);
        $response->assertJson(["message" => "Message destroyed successfully"]);
    }


    public function testDestroyMessageFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('DELETE', 'api/messages/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    public function testDestroyMessageFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/messages/1", [
            "message" => "test update message"
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'message' => 'Message not found'
        ]);
    }

}
