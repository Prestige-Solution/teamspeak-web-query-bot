<?php

namespace Tests\Feature\channels;

use App\Models\ts3BotWorkers\ts3BotWorkerChannelsCreate;
use App\Models\ts3BotWorkers\ts3BotWorkerChannelsRemove;
use App\Models\User;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateJobChannelCreatorFactory;
use Database\Factories\CreateJobChannelRemoverFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', 1)->first();
    }

    public function test_view_created_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelCreatorFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('channel.view.channelJobs'));

        $response->assertOk();
        $response->assertSeeText('UnitTest');
        $response->assertSeeText('Community');
        $response->assertSeeText('Channel Admin');
        $response->assertSeeText('Move client to channel');
        $response->assertSeeText('Create permanent channel');
        $response->assertSeeText('Client enters channel');
    }

    public function test_post_create_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $createChannelArray = CreateJobChannelCreatorFactory::new()->make()->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.channelJob'), $createChannelArray);

        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = ts3BotWorkerChannelsCreate::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('clientmoved', $checkDB->first()->on_event);
        $this->assertEquals(9, $checkDB->first()->channel_cgid);
        $this->assertEquals(54, $checkDB->first()->on_cid);
    }

    public function test_post_update_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelCreatorFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $updateChannelArray = CreateJobChannelCreatorFactory::new()->make(['notify_message_server_group_message'=>'edited', 'action_min_clients'=>10])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.channelJob'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = ts3BotWorkerChannelsCreate::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('clientmoved', $checkDB->first()->on_event);
        $this->assertEquals(9, $checkDB->first()->channel_cgid);
        $this->assertEquals(54, $checkDB->first()->on_cid);
        $this->assertEquals('edited', $checkDB->first()->notify_message_server_group_message);
        $this->assertEquals(10, $checkDB->first()->action_min_clients);
    }

    public function test_post_delete_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelCreatorFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelJob', ['id' => 1]));
        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully deleted']);

        $checkDB = ts3BotWorkerChannelsCreate::query()->get();
        $this->assertEquals(0, $checkDB->count());

        $response = $this->actingAs($this->user)->get(route('channel.view.channelJobs'));

        $response->assertOk();
        $response->assertSeeText('No jobs have been added yet');
    }

    public function test_view_created_channel_remover_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelRemoverFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('channel.view.listChannelRemover'));

        $response->assertOk();
        $response->assertSeeText('UnitTest');
        $response->assertSeeText('1 minute/s');
    }

    public function test_post_update_channel_remover_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelRemoverFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $updateChannelArray = CreateJobChannelRemoverFactory::new()->make(['channel_max_seconds_empty'=>2, 'channel_max_time_format'=>'h'])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.newChannelRemover'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = ts3BotWorkerChannelsRemove::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('h', $checkDB->first()->channel_max_time_format);
        $this->assertEquals(2 * 60 * 60, $checkDB->first()->channel_max_seconds_empty);
    }

    public function test_post_delete_channel_remover_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelRemoverFactory::new()->create();
        User::query()->where('id', 1)->update(['default_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelRemover', ['id' => 1]));

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully deleted']);

        $checkDB = ts3BotWorkerChannelsRemove::query()->get();
        $this->assertEquals(0, $checkDB->count());

        $response = $this->actingAs($this->user)->get(route('channel.view.listChannelRemover'));

        $response->assertOk();
        $response->assertSeeText('There are no channels added yet.');
    }

    public function update_user(): void
    {
        $this->user = User::query()->where('id', 1)->first();
    }
}
