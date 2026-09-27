<?php

namespace Tests\Feature\channels;

use App\Models\tsBotWorkers\tsBotWorkerChannelsCreate;
use App\Models\tsBotWorkers\tsBotWorkerChannelsRemove;
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

        $this->user = User::query()->where('id', '=', 1)->first();
    }

    public function test_view_created_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelCreatorFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
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

    public function test_view_channel_options_tree_hierarchy()
    {
        CreateServerFactory::new()->create();
        // Root channel
        CreateChannelFactory::new()->create(['cid' => 10, 'pid' => 0, 'channel_name' => 'channel-1']);
        // Sub channels level 1
        CreateChannelFactory::new()->create(['cid' => 11, 'pid' => 10, 'channel_order' => 1, 'channel_name' => 'channel 1-1']);
        CreateChannelFactory::new()->create(['cid' => 12, 'pid' => 10, 'channel_order' => 2, 'channel_name' => 'channel 1-2']);
        // Sub channels level 2 under channel 1-2
        CreateChannelFactory::new()->create(['cid' => 13, 'pid' => 12, 'channel_order' => 1, 'channel_name' => 'channel 2-1']);
        CreateChannelFactory::new()->create(['cid' => 14, 'pid' => 12, 'channel_order' => 2, 'channel_name' => 'channel 2-2']);
        // Deep sub channel level 3 under channel 2-1
        CreateChannelFactory::new()->create(['cid' => 15, 'pid' => 13, 'channel_order' => 1, 'channel_name' => 'channel 3-1']);

        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('channel.view.channelJobs'));
        $response->assertOk();

        $response->assertSee('channel-1');
        $response->assertSee("├──\u{00A0}channel 1-1");
        $response->assertSee("└──\u{00A0}channel 1-2");
        $response->assertSee("\u{00A0}\u{00A0}\u{00A0}\u{00A0}├──\u{00A0}channel 2-1");
        $response->assertSee("\u{00A0}\u{00A0}\u{00A0}\u{00A0}│\u{00A0}\u{00A0}\u{00A0}└──\u{00A0}channel 3-1");
        $response->assertSee("\u{00A0}\u{00A0}\u{00A0}\u{00A0}└──\u{00A0}channel 2-2");
    }

    public function test_post_create_channel_creator_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $createChannelArray = CreateJobChannelCreatorFactory::new()->make()->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.channelJob'), $createChannelArray);

        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = tsBotWorkerChannelsCreate::query()->get();
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
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $updateChannelArray = CreateJobChannelCreatorFactory::new()->make(['notify_message_server_group_message'=>'edited', 'action_min_clients'=>10])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.channelJob'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = tsBotWorkerChannelsCreate::query()->get();
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
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelJob', ['id' => 1]));
        $response->assertRedirectToRoute('channel.view.channelJobs');
        $response->assertSessionHas(['success' => 'The job was successfully deleted']);

        $checkDB = tsBotWorkerChannelsCreate::query()->get();
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
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
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
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $updateChannelArray = CreateJobChannelRemoverFactory::new()->make(['channel_max_seconds_empty'=>2, 'channel_max_time_format'=>'h'])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.newChannelRemover'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = tsBotWorkerChannelsRemove::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('h', $checkDB->first()->channel_max_time_format);
        $this->assertEquals(2 * 60 * 60, $checkDB->first()->channel_max_seconds_empty);

        $updateChannelArray = CreateJobChannelRemoverFactory::new()->make(['channel_max_seconds_empty'=>2, 'channel_max_time_format'=>'d'])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.newChannelRemover'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = tsBotWorkerChannelsRemove::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('d', $checkDB->last()->channel_max_time_format);
        $this->assertEquals(2 * 24 * 60 * 60, $checkDB->first()->channel_max_seconds_empty);

        $updateChannelArray = CreateJobChannelRemoverFactory::new()->make(['channel_max_seconds_empty'=>2])->toArray();
        $response = $this->actingAs($this->user)->post(route('channel.upsert.newChannelRemover'), $updateChannelArray);

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully updated']);

        $checkDB = tsBotWorkerChannelsRemove::query()->get();
        $this->assertEquals(1, $checkDB->count());
        $this->assertEquals('m', $checkDB->last()->channel_max_time_format);
        $this->assertEquals(2 * 60, $checkDB->first()->channel_max_seconds_empty);
    }

    public function test_post_delete_channel_remover_job()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        CreateJobChannelRemoverFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelRemover', ['id' => 1]));

        $response->assertRedirectToRoute('channel.view.listChannelRemover');
        $response->assertSessionHas(['success' => 'The job was successfully deleted']);

        $checkDB = tsBotWorkerChannelsRemove::query()->get();
        $this->assertEquals(0, $checkDB->count());

        $response = $this->actingAs($this->user)->get(route('channel.view.listChannelRemover'));

        $response->assertOk();
        $response->assertSeeText('There are no channels added yet.');
    }

    public function test_post_create_channel_creator_job_validation_fails_for_missing_fields()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.upsert.channelJob'), []);
        $response->assertSessionHasErrors(['on_cid', 'on_event', 'action_id', 'action_user_id', 'channel_cgid', 'channel_template_cid', 'action_min_clients', 'create_max_channels', 'is_active']);
    }

    public function test_post_create_channel_remover_job_validation_fails_for_invalid_format()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.upsert.newChannelRemover'), [
            'channel_cid' => 1,
            'channel_max_seconds_empty' => 5,
            'channel_max_time_format' => 'invalid_format',
            'is_active' => true,
        ]);
        $response->assertSessionHasErrors(['channel_max_time_format']);
    }

    public function test_post_delete_channel_creator_job_validation_fails_for_non_existent_id()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelJob'), ['id' => 999]);
        $response->assertSessionHasErrors(['id']);
    }

    public function test_post_delete_channel_remover_job_validation_fails_for_non_existent_id()
    {
        CreateServerFactory::new()->create();
        User::query()->where('id', 1)->update(['active_server_id' => 1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->post(route('channel.delete.channelRemover'), ['id' => 999]);
        $response->assertSessionHasErrors(['id']);
    }

    public function test_delete_channel_also_deletes_subchannels_and_jobs_on_subchannels(): void
    {
        CreateServerFactory::new()->create(['id' => 1]);
        CreateServerFactory::new()->create(['id' => 2]);
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();

        // Server 1 channels
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 10, 'pid' => 0, 'channel_name' => 'Root 1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 11, 'pid' => 10, 'channel_name' => 'Sub 1-1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 12, 'pid' => 11, 'channel_name' => 'Sub 1-1-1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 20, 'pid' => 0, 'channel_name' => 'Root 2']);

        // Server 1 jobs
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 10]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 11]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 12]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 20]);

        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 10]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 11]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 12]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 20]);

        // Server 2 channels & jobs (isolation check)
        CreateChannelFactory::new()->create(['server_id' => 2, 'cid' => 10, 'pid' => 0, 'channel_name' => 'Server 2 Root 1']);
        CreateChannelFactory::new()->create(['server_id' => 2, 'cid' => 11, 'pid' => 10, 'channel_name' => 'Server 2 Sub 1-1']);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 2, 'on_cid' => 11]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 2, 'channel_cid' => 11]);

        $reflection = new \ReflectionClass(\App\Http\Controllers\bot\TsBotController::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $serverProp = $reflection->getProperty('serverId');
        $serverProp->setValue($instance, 1);

        $method = $reflection->getMethod('deleteChannel');
        $method->invoke($instance, 10);

        // Assert Server 1 deleted channels & subchannels
        $server1Channels = \App\Models\tsBot\tsChannel::query()->where('server_id', 1)->pluck('cid')->all();
        $this->assertEquals([20], $server1Channels);

        // Assert Server 1 creator jobs deleted for 10, 11, 12 but kept for 20
        $server1CreateJobs = tsBotWorkerChannelsCreate::query()->where('server_id', 1)->pluck('on_cid')->all();
        $this->assertEquals([20], $server1CreateJobs);

        // Assert Server 1 remover jobs deleted for 10, 11, 12 but kept for 20
        $server1RemoveJobs = tsBotWorkerChannelsRemove::query()->where('server_id', 1)->pluck('channel_cid')->all();
        $this->assertEquals([20], $server1RemoveJobs);

        // Assert Server 2 channels and jobs remain untouched
        $server2Channels = \App\Models\tsBot\tsChannel::query()->where('server_id', 2)->pluck('cid')->all();
        $this->assertEquals([10, 11], $server2Channels);

        $server2CreateJobs = tsBotWorkerChannelsCreate::query()->where('server_id', 2)->pluck('on_cid')->all();
        $this->assertEquals([11], $server2CreateJobs);

        $server2RemoveJobs = tsBotWorkerChannelsRemove::query()->where('server_id', 2)->pluck('channel_cid')->all();
        $this->assertEquals([11], $server2RemoveJobs);
    }

    public function test_delete_subchannel_only_deletes_subchannel_and_its_descendants(): void
    {
        CreateServerFactory::new()->create(['id' => 1]);
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();

        // Server 1 channels
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 10, 'pid' => 0, 'channel_name' => 'Root']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 11, 'pid' => 10, 'channel_name' => 'Sub 1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 12, 'pid' => 11, 'channel_name' => 'Sub 1-1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 13, 'pid' => 10, 'channel_name' => 'Sub 2']);

        // Jobs
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 10]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 11]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 12]);
        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 13]);

        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 10]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 11]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 12]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 13]);

        $reflection = new \ReflectionClass(\App\Http\Controllers\bot\TsBotController::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $serverProp = $reflection->getProperty('serverId');
        $serverProp->setValue($instance, 1);

        $method = $reflection->getMethod('deleteChannel');
        $method->invoke($instance, 11);

        // Channels 10 and 13 should remain; 11 and 12 should be deleted
        $remainingChannels = \App\Models\tsBot\tsChannel::query()->where('server_id', 1)->pluck('cid')->all();
        $this->assertEquals([10, 13], $remainingChannels);

        $remainingCreateJobs = tsBotWorkerChannelsCreate::query()->where('server_id', 1)->pluck('on_cid')->all();
        $this->assertEquals([10, 13], $remainingCreateJobs);

        $remainingRemoveJobs = tsBotWorkerChannelsRemove::query()->where('server_id', 1)->pluck('channel_cid')->all();
        $this->assertEquals([10, 13], $remainingRemoveJobs);
    }

    public function test_clearing_worker_delete_channel_from_db_cascades_to_subchannels_and_jobs(): void
    {
        CreateServerFactory::new()->create(['id' => 1]);
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();

        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 10, 'pid' => 0, 'channel_name' => 'Root']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 11, 'pid' => 10, 'channel_name' => 'Sub 1']);
        CreateChannelFactory::new()->create(['server_id' => 1, 'cid' => 12, 'pid' => 11, 'channel_name' => 'Sub 1-1']);

        CreateJobChannelCreatorFactory::new()->create(['server_id' => 1, 'on_cid' => 12]);
        CreateJobChannelRemoverFactory::new()->create(['server_id' => 1, 'channel_cid' => 12]);

        $clearingWorker = new \App\Http\Controllers\botWorker\ClearingWorkerController(1);
        $reflection = new \ReflectionClass($clearingWorker);
        $method = $reflection->getMethod('deleteChannelFromDB');
        $method->invoke($clearingWorker, 10);

        $this->assertEquals(0, \App\Models\tsBot\tsChannel::query()->where('server_id', 1)->count());
        $this->assertEquals(0, tsBotWorkerChannelsCreate::query()->where('server_id', 1)->count());
        $this->assertEquals(0, tsBotWorkerChannelsRemove::query()->where('server_id', 1)->count());
    }

    public function update_user(): void
    {
        $this->user = User::query()->where('id', '=', 1)->first();
    }
}
