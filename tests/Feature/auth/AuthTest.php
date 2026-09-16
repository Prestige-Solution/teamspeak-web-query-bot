<?php

namespace Tests\Feature\auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    /**
     * A basic feature test example.
     */
    public function test_can_login(): void
    {
        $response = $this->actingAs($this->user)->post(Route('logging-in', [
            'nickname' => $this->user->nickname,
            'password' => 'test',
            '_token' => csrf_token(),
        ]));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('backend.view.dashboard');
    }

    public function test_can_login_failed(): void
    {
        $response = $this->actingAs($this->user)->post(Route('logging-in', [
            'nickname' => $this->user->nickname,
            'password' => 'false',
            '_token' => csrf_token(),
        ]));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('public.view.login');
        $response->assertSessionHasErrors(['error'=>'Incorrect nickname or password']);
    }

    public function test_can_update_password(): void
    {
        $response = $this->actingAs($this->user)->post(Route('backend.update.changePassword', [
            'CurrentPassword' => 'test',
            'NewPassword' => 'testNew',
            'NewPassword_confirmation' => 'testNew',
            '_token' => csrf_token(),
        ]));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('backend.view.dashboard');
        $response->assertSessionHas(['success'=>'Password changed successfully.']);

        $response = $this->actingAs($this->user)->post(Route('logging-in', [
            'nickname' => $this->user->nickname,
            'password' => 'testNew',
            '_token' => csrf_token(),
        ]));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('backend.view.dashboard');
    }

    public function test_can_update_password_failed(): void
    {
        $response = $this->actingAs($this->user)->post(Route('backend.update.changePassword', [
            'CurrentPassword' => 'test',
            'NewPassword' => 'testNew',
            '_token' => csrf_token(),
        ]));

        $response->assertStatus(302);
        $response->assertRedirectBack();
        $response->assertSessionHasErrors(['NewPassword'=>'The new password does not match']);
    }

    public function test_can_logout(): void
    {
        $response = $this->actingAs($this->user)->get(Route('logout'));

        $response->assertStatus(302);
        $response->assertRedirectToRoute('public.view.login');
    }
}
