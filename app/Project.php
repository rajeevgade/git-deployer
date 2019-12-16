<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'branch', 'path', 'status', 'secret', 'pre_hook', 'email_result', 'user_id', 'last_hook_status', 'last_hook_time', 'last_hook_duration', 'last_hook_log'
    ];
}
