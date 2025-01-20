<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $casts = [
        'value' => AsArrayObject::class
    ];
}
