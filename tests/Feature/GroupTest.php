<?php

namespace Tests\Feature;

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;

class GroupTest extends BaseTestCase
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

    public function testCreateGroupSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json('POST',"/api/groups", ["name" => "groupName", "purpose" => "group purpose"],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertCreated();
        $response->assertJson(['success' => 'group record created successfully']);
    }

    public function testCreateGroupFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('POST', 'api/groups');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    public function testGetGroupsSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $this->json('POST',"/api/groups", ["name" => "groupName", "purpose" => "group purpose"],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->get("/api/groups", ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(["message" =>[["name" => "groupName"]]]);
    }

    public function testGetGroupsFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/groups');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    public function testGetGroupsFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/groups", ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson(["error" => "No Groups"]);
    }


    public function testGetGroupSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $group = $this->json('POST',"/api/groups", ["name" => "groupName", "purpose" => "group purpose"],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->get("/api/groups/{$group['message']['id']}", ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(["success" => ["name" => "groupName"]]);
    }

    public function testGetGroupFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/groups/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    public function testGetGroupFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/groups/1", ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson(["error" => "Record not found"]);
    }


    public function testUpdateGroupSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $group = $this->json('POST',"/api/groups", ["name" => "groupName", "purpose" => "group purpose"],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json('PATCH',"/api/groups/{$group['message']['id']}",
            ["name" => "test group update", "purpose" => ""],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(['success' => 'group updated successfully']);
    }

    public function testUpdateGroupFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/groups/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    public function testUpdateGroupFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/groups/1", ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson(["error" => "Record not found"]);
    }

    public function testDestroyGroupSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $group = $this->json('POST',"/api/groups", ["name" => "groupName", "purpose" => "group purpose"],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/groups/{$group['message']['id']}", [], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(['success' => 'Group deleted successfully']);
    }


    public function testDestroyGroupFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('DELETE', 'api/groups/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    public function testDestroyGroupFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/groups/1", [
            "message" => "test update message"
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'message' => 'Group not found'
        ]);
    }
}
