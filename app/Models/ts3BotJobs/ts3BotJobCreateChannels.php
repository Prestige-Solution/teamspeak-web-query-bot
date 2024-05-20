<?php

namespace App\Models\ts3BotJobs;

use App\Models\category\catBotJobType;
use App\Models\ts3Bot\ts3Channel;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotEvents\ts3BotAction;
use App\Models\ts3BotEvents\ts3BotActionUser;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ts3BotJobCreateChannels extends Model
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
        'notify_message_server_group',
        'notify_message_server_group_sgid',
        'notify_message_server_group_message',
    ];

    public function rel_servers()
    {
        return $this->hasMany(ts3ServerConfig::class,'id','server_id');
    }
    public function rel_types()
    {
        return $this->hasMany(catBotJobType::class,'id','type_id');
    }
    public function rel_actions()
    {
        return $this->hasMany(ts3BotAction::class,'id','action_id');
    }
    public function rel_action()
    {
        return $this->hasOne(ts3BotAction::class,'id','action_id');
    }
    public function rel_action_users()
    {
        return $this->hasMany(ts3BotActionUser::class,'id','action_user_id');
    }
    public function rel_action_user()
    {
        return $this->hasOne(ts3BotActionUser::class,'id','action_user_id');
    }
    public function rel_channels()
    {
        return $this->hasMany(ts3Channel::class,['cid','server_id'],['on_cid','server_id']);
    }
}
