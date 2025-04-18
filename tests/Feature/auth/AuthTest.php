<?php

namespace Tests\Feature\auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id','=', 1)->first();
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
        $response->assertRedirectToRoute('backend.view.botControlCenter');
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
        $response->assertSessionHasErrors(['error'=>'Name oder Passwort falsch']);
    }
}
