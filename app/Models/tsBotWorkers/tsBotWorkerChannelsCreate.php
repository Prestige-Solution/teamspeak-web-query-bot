<?php

namespace App\Models\tsBotWorkers;

use App\Models\category\catBotJobType;
use App\Models\tsBot\tsChannel;
use App\Models\tsBot\tsChannelGroup;
use App\Models\tsBot\tsServerConfig;
use App\Models\tsBot\tsServerGroup;
use App\Models\tsBotEvents\tsBotAction;
use App\Models\tsBotEvents\tsBotActionUser;
use App\Models\tsBotEvents\tsBotEvent;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Awobaz\Compoships\Database\Eloquent\Relations\HasOne;
use Database\Factories\CreateJobChannelCreatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tsBotWorkerChannelsCreate extends Model
{
    use HasFactory, Compoships;

    public const int textMessage = 1;

    public const int pokeMessage = 2;

    protected $fillable = [
        'server_id',
        'type_id',
        'on_cid',
        'on_event',
        'action_id',
        'action_min_clients',
        'create_max_channels',
        'action_user_id',
        'channel_cgid',
        'channel_template_cid',
        'is_notify_message_server_group',
        'notify_message_server_group_sgid',
        'notify_message_server_group_message',
        'notify_option',
        'is_active',
    ];

    protected static function newFactory(): CreateJobChannelCreatorFactory
    {
        return CreateJobChannelCreatorFactory::new();
    }

    public function rel_servers(): HasMany
    {
        return $this->hasMany(tsServerConfig::class, 'id', 'server_id');
    }

    public function rel_types(): HasMany
    {
        return $this->hasMany(catBotJobType::class, 'id', 'type_id');
    }

    public function rel_actions(): HasMany
    {
        return $this->hasMany(tsBotAction::class, 'id', 'action_id');
    }

    public function rel_action(): HasOne
    {
        return $this->hasOne(tsBotAction::class, 'id', 'action_id');
    }

    public function rel_action_users(): HasMany
    {
        return $this->hasMany(tsBotActionUser::class, 'id', 'action_user_id');
    }

    public function rel_action_user(): HasOne
    {
        return $this->hasOne(tsBotActionUser::class, 'id', 'action_user_id');
    }

    public function rel_channels(): HasMany
    {
        return $this->hasMany(tsChannel::class, ['cid', 'server_id'], ['on_cid', 'server_id']);
    }

    public function rel_template_channel(): HasOne
    {
        return $this->hasOne(tsChannel::class, ['cid', 'server_id'], ['channel_template_cid', 'server_id']);
    }

    public function rel_bot_event(): HasOne
    {
        return $this->hasOne(tsBotEvent::class, 'event_ts', 'on_event');
    }

    public function rel_cgid(): HasOne
    {
        return $this->hasOne(tsChannelGroup::class, ['cgid', 'server_id'], ['channel_cgid', 'server_id']);
    }

    public function rel_sgid(): HasOne
    {
        return $this->hasOne(tsServerGroup::class, ['sgid', 'server_id'], ['notify_message_server_group_sgid', 'server_id']);
    }

    public function rel_pid(): HasMany
    {
        return $this->hasMany(tsChannel::class, ['pid','server_id'], ['on_cid', 'server_id']);
    }
}
