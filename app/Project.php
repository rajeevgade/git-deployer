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
        'name', 'branch', 'path', 'status', 'secret', 'pre_hook', 'post_hook', 'email_result', 'last_hook_status', 'last_hook_time', 'last_hook_duration', 'last_hook_log', 'ssh_ip', 'ssh_username', 'ssh_password'
    ];

    public $timestamps = false;

    /**
     * Return model validation rules
     *
     * @return array
     */
    public static function getRules($merge = []) {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'branch' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string'],
            'status' => ['required'],
            'secret' => ['required', 'string'],
            'pre_hook' => ['nullable', 'string'],
            'post_hook' => ['nullable', 'string'],
            'email_result' => ['nullable', 'string'],
            'last_hook_status' => ['nullable', 'string'],
            'last_hook_time' => ['nullable', 'string'],
            'last_hook_duration' => ['nullable'],
            'last_hook_log' => ['nullable', 'string'],
            'ssh_ip' => ['nullable', 'string'],
            'ssh_username' => ['nullable', 'string'],
            'ssh_password' => ['nullable', 'string'],
        ], $merge);
    }


}
