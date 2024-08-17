<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_menus',
        "id_roles",
        'created_at',
        'updated_at'
    ];
}
