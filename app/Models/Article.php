<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $casts = [
        'source' => AsArrayObject::class,
        'published_date' => 'datetime'
    ];
}
