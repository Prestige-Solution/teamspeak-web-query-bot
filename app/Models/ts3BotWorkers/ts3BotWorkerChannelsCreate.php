<?php

namespace App\Models\ts3BotWorkers;

use App\Models\category\catBotJobType;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ChannelGroup;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3Bot\ts3ServerGroup;
use App\Models\ts3BotEvents\ts3BotAction;
use App\Models\ts3BotEvents\ts3BotActionUser;
use App\Models\ts3BotEvents\ts3BotEvent;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Awobaz\Compoships\Database\Eloquent\Relations\HasOne;
use Database\Factories\CreateJobChannelCreatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotWorkerChannelsCreate extends Model
{
    use HasFactory, Compoships;

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
        'channel_template_id',
        'channel_template_cid',
        'is_notify_message_server_group',
        'notify_message_server_group_sgid',
        'notify_message_server_group_message',
        'is_active',
    ];

    protected static function newFactory(): CreateJobChannelCreatorFactory
    {
        return CreateJobChannelCreatorFactory::new();
    }

    public function rel_servers(): HasMany
    {
        return $this->hasMany(ts3ServerConfig::class, 'id', 'server_id');
    }

    public function rel_types(): HasMany
    {
        return $this->hasMany(catBotJobType::class, 'id', 'type_id');
    }

    public function rel_actions(): HasMany
    {
        return $this->hasMany(ts3BotAction::class, 'id', 'action_id');
    }

    public function rel_action(): HasOne
    {
        return $this->hasOne(ts3BotAction::class, 'id', 'action_id');
    }

    public function rel_action_users(): HasMany
    {
        return $this->hasMany(ts3BotActionUser::class, 'id', 'action_user_id');
    }

    public function rel_action_user(): HasOne
    {
        return $this->hasOne(ts3BotActionUser::class, 'id', 'action_user_id');
    }

    public function rel_channels(): HasMany
    {
        return $this->hasMany(ts3Channel::class, ['cid', 'server_id'], ['on_cid', 'server_id']);
    }

    public function rel_template_channel(): HasOne
    {
        return $this->hasOne(ts3Channel::class, ['cid', 'server_id'], ['channel_template_id', 'server_id']);
    }

    public function rel_bot_event(): HasOne
    {
        return $this->hasOne(ts3BotEvent::class, 'event_ts', 'on_event');
    }

    public function rel_cgid(): HasOne
    {
        return $this->hasOne(ts3ChannelGroup::class, ['cgid', 'server_id'], ['channel_cgid', 'server_id']);
    }

    public function rel_sgid(): HasOne
    {
        return $this->hasOne(ts3ServerGroup::class, ['sgid', 'server_id'], ['notify_message_server_group_sgid', 'server_id']);
    }

    public function rel_pid(): HasMany
    {
        return $this->hasMany(ts3Channel::class, 'pid', 'on_cid');
    }
}
