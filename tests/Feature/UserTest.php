<?php

namespace Tests\Feature;

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;

class UserTest extends BaseTestCase
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


    /**
     * A Success test upon user registration
     *
     * @return void
     */
    public function testRegisterUserSuccess201()
    {
        $response = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response->assertCreated();
        $response->assertJson(['success' => ['message' => 'User successfully Registered']]);
    }

    /**
     * A 400 Failure test upon user registration
     *
     * @return void
     */
    public function testRegisterUserFailure400()
    {
        $response = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password13"],
            ["Content-Type" => "application/json"]);
        $response->assertStatus(400);
        $response->assertJson([
            "error" => [
                "confirm_password" => [
                    "The confirm password and password must match."
                ]]

        ]);
    }

    /**
     * A Success test upon user login
     *
     * @return void
     */
    public function testLoginUserSuccess200()
    {
        $this->withExceptionHandling();
//        $user = factory('App\User')->create([
//            "password" => "Manuel123",]);
        $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json('POST', 'api/login', [
//            'email' => $user->email,
            'email' => 'example@gmail.com',
            'password' => 'Password123',
        ]);
        $response->assertOk();
        $response->assertJson([
            "success" => ['message' => 'LoggedIn successfully']
        ]);
    }


    /**
     * A 401 Failure test upon user login
     *
     * @return void
     */
    public function testLoginUserFailure401()
    {
        $this->withExceptionHandling();
        $user = factory(User::class)->create([
            "password" => "Password123"
        ]);
        $response = $this->json('POST', 'api/login', [
            "email" => $user->email,
            "password" => "Password",
        ]);
        $response->assertStatus(401);
        $response->assertJson([
            "error" => "Unauthorised, invalid credentials provided"
        ]);
    }


    /**
     * A Success test upon getting all users in storage
     *
     * @return void
     */
    public function testGetUsersSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/users",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson([
            "success" => ["total" => 1]
        ]);
    }


    /**
     * A 401 Failure test upon getting all users in storage
     *
     * @return void
     */
    public function testGetUsersFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/users');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    /**
     * A Success test upon getting a single user in storage
     *
     * @return void
     */
    public function testGetUserSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/users/1",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson([
            "success" => ['email_verified_at' => NULL]
        ]);
    }


    /**
     * A 401 test upon getting a single user in storage
     *
     * @return void
     */
    public function testGetUserFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/users/1');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

    /**
     * A 404 Failure test upon getting a single user in storage
     *
     * @return void
     */
    public function testGetUserFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/users/5",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'error' => 'Record not found'
        ]);
    }


    /**
     * A Success test upon updating a user's info
     *
     * @return void
     */
    public function testUpdateUsersSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("PATCH", "/api/users/{$user['success']['id']}/update", [
            "name" => "Michael Dominic",
            "password" => "",
            "confirm_password" => ""
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(["message" => "user record updated successfully"]);
    }


    /**
     * A 401 Failure test upon updating a user's info
     *
     * @return void
     */
    public function testUpdateUsersFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('PATCH', 'api/users/1/update');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    /**
     * A 404 Failure test upon updating a user's info
     *
     * @return void
     */
    public function testUpdateUsersFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $user_id = $user['success']['id'] + 1;
        $response = $this->json("PATCH", "/api/users/$user_id/update", [
            "name" => "Michael Dominic",
            "password" => "",
            "confirm_password" => ""
        ], ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson([
            'error' => 'user not found'
        ]);
    }


    /**
     * A Success test upon getting soft deleted users
     *
     * @return void
     */
    public function testDeactivatedUsersSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $this->json("DELETE", "/api/users/{$user['success']['id']}/delete", [],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->json("GET", "/api/trash/users",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
    }


    /**
     * A 404 Failure test upon getting soft deleted users
     *
     * @return void
     */
    public function testDeactivatedUsersFailure404()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->get("/api/trash/users",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertNotFound();
        $response->assertJson(['error' => 'No trashed users found']);
    }


    /**
     * A 401 Failure test upon getting soft deleted users
     *
     * @return void
     */
    public function testDeactivatedUsersFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/trash/users');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    /**
     * A Success test upon soft deleting a users
     *
     * @return void
     */
    public function testDeactivateUserSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/users/{$user['success']['id']}/delete", [],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(['success' => ['message' => 'user record deleted successfully']]);
    }


    /**
     * A 401 Failure test upon soft deleting a users
     *
     * @return void
     */
    public function testDeactivateUserFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('DELETE', 'api/users/1/delete');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    /**
     * A Success test upon restoring a soft deleted user
     *
     * @return void
     */
    public function testRestoreUserSuccess200()
    {
        $this->withExceptionHandling();
        $user = $this->json('POST', "/api/register",
            ["name" => "Manuel Dominic",
                "username" => "Manuel",
                "email" => "example@gmail.com",
                "password" => "Password123",
                "confirm_password" => "Password123"],
            ["Content-Type" => "application/json"]);
        $response = $this->json("DELETE", "/api/users/{$user['success']['id']}/delete", [],
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response = $this->get("/api/users/{$user['success']['id']}/restore",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(['success' => ['message' => 'user record restored successfully']]);
    }


    /**
     * A 401 Failure test upon restoring a soft deleted user
     *
     * @return void
     */
    public function testRestoreUserFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/users/1/restore');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }


    /**
     * A Success test upon getting all both soft deleted and non deleted users
     *
     * @return void
     */
    public function testHistoricalUsersSuccess200()
    {$user = $this->json('POST', "/api/register",
        ["name" => "Manuel Dominic",
            "username" => "Manuel",
            "email" => "example@gmail.com",
            "password" => "Password123",
            "confirm_password" => "Password123"],
        ["Content-Type" => "application/json"]);
        $response = $this->get("/api/records/users",
            ["Authorization" => "Bearer {$user['success']['access_token']}", "Content-Type" => "application/json"]);
        $response->assertOk();
        $response->assertJson(['success' => ['message' => 'Historical user records']]);
    }


    /**
     * A 401 Failure test upon getting all both soft deleted and non deleted users
     *
     * @return void
     */
    public function testHistoricalUsersFailure401()
    {
        $this->withExceptionHandling();
        $response = $this->json('GET', 'api/records/users');
        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }

}
