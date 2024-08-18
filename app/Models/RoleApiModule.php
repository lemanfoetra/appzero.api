<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleApiModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_roles',
        "id_api_module",
        'created_at',
        'updated_at'
    ];
}
