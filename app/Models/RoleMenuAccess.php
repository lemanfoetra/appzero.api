<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenuAccess extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'id_menus',
        "id_roles",
        "access_code",
        'created_at',
        'updated_at'
    ];
}
