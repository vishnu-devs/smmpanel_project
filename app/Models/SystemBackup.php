<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'filename',
        'type',
        'size_bytes',
        'status',
        'error_message',
    ];
}
