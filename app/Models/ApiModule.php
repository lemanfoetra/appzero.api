<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_menus',
        'name',
        'method',
        'key',
        'url',
        'query_param',
        'description',
        'header',
        'body',
        'response',
        'created_at',
        'updated_at'
    ];
}
