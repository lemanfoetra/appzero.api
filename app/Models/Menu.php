<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_parrent',
        'menu',
        'link',
        'icon',
        'urutan',
        'created_at',
        'updated_at'
    ];
}
