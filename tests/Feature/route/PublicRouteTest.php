<?php

namespace Tests\Feature\route;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_view_login(): void
    {
        $response = $this->get(route('public.view.login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Web Query Bot | Login');
    }

    public function test_can_view_control_center_failed(): void
    {
        $response = $this->get('/dashboard/control-center');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_password_reset_failed(): void
    {
        $response = $this->get('/dashboard/password-reset');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_bot_logs_failed(): void
    {
        $response = $this->get('/dashboard/logs/bot-logs');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_server_list_failed(): void
    {
        $response = $this->get('/server/list');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_channels_channel_creator_job_list_failed(): void
    {
        $response = $this->get('/channels/channel-creator/job-list');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_channels_channel_remover_job_list_failed(): void
    {
        $response = $this->get('/channels/channel-remover/job-list');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_worker_afk_settings_failed(): void
    {
        $response = $this->get('/worker/afk/settings');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_worker_police_settings_failed(): void
    {
        $response = $this->get('/worker/police/settings');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_worker_bad_nicknames_list_failed(): void
    {
        $response = $this->get('/worker/bad-nicknames/list');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }

    public function test_can_view_banner_list_failed(): void
    {
        $response = $this->get('/banner/list');

        $response->assertStatus(302);
        $response->assertRedirect(route('public.view.login'));
    }
}
