<?php

namespace Tests\Feature\worker;

use App\Http\Controllers\ts3Config\BadNameController;
use App\Models\sys\badName;
use App\Models\User;
use Database\Factories\CreateBadNicknameFactory;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Database\Factories\CreateWorkerPoliceSettingsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadNicknameTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', 1)->first();
    }

    public function test_get_view_bad_nickname()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('worker.view.badNames'));
        $response->assertOk();
        $response->assertSee('Bad Name List');
    }

    public function test_post_create_new_bad_nickname()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $updateArray = CreateBadNicknameFactory::new()->make()->toArray();
        $currentDB = badName::query()->get();
        $response = $this->actingAs($this->user)->post(route('worker.create.newBadName'), $updateArray);
        $response->assertRedirectToRoute('worker.view.badNames');

        //check
        $db = badName::query()->get();
        $this->assertCount($currentDB->count() + 1, $db);
        $this->assertEquals('Factory Test', $db->last()->description);
        $this->assertEquals('factory', $db->last()->value);
    }

    public function test_post_delete_bad_nickname()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create();
        CreateBadNicknameFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $currentDB = badName::query()->get();

        $response = $this->actingAs($this->user)->post(route('worker.delete.badName'), ['id'=>$currentDB->last()->id]);
        $response->assertRedirectToRoute('worker.view.badNames');

        //check
        $db = badName::query()->get();
        $this->assertCount($currentDB->count() - 1, $db);
    }

    public function test_check_bad_nickname()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateWorkerPoliceSettingsFactory::new()->create(['is_bad_name_protection_global_list_active'=>true]);
        CreateBadNicknameFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $badNameController = new BadNameController();
        $result = $badNameController->checkBadName('admin', $this->user->default_server_id);
        $this->assertTrue($result);

        $result = $badNameController->checkBadName('Administrator', $this->user->default_server_id);
        $this->assertTrue($result);

        $result = $badNameController->checkBadName('Factory', $this->user->default_server_id);
        $this->assertTrue($result);

        $result = $badNameController->checkBadName('factory', $this->user->default_server_id);
        $this->assertTrue($result);

        $result = $badNameController->checkBadName('Hans', $this->user->default_server_id);
        $this->assertFalse($result);

        $result = $badNameController->checkBadName('Rick', $this->user->default_server_id);
        $this->assertFalse($result);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', 1)->first();
    }
}
