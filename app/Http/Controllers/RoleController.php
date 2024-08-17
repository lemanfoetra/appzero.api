<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{

    public function index()
    {
        try {
            $roles = DB::table('roles')->get();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $roles,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $role = Role::create([
                'name'      => $request->name,
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'role'  => $role,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'       => false,
                'message'       => $th->getMessage(),
                'message_type'  => 'string',
                'data'          => [],
            ], 200);
        }
    }


    public function show($roleId)
    {
        try {
            $role = DB::table('roles')
                ->select([
                    'id',
                    'name',
                    'created_at',
                    'updated_at'
                ])
                ->where('id', $roleId)
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'role'  => $role
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
                'total'     => 0,
            ], 500);
        }
    }



    public function update($roleId, Request $request)
    {
        try {
            $validation = [
                'name' => "required|string|max:255|unique:roles,name,{$roleId}",
            ];
            $validator = Validator::make($request->all(), $validation);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $data = [
                'name'      => $request->name,
            ];

            DB::table('roles')
                ->where('id', $roleId)
                ->update($data);

            $role = DB::table('roles')
                ->where('id', $roleId)
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'role'  => $role,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'       => false,
                'message'       => $th->getMessage(),
                'message_type'  => 'string',
                'data'          => [],
            ], 500);
        }
    }


    public function delete($roleId)
    {
        try {
            DB::table('roles')
                ->where('id', $roleId)
                ->limit(1)
                ->delete();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'       => false,
                'message'       => $th->getMessage(),
                'message_type'  => 'string',
                'data'          => [],
            ], 500);
        }
    }


    public function menus()
    {
        try {
            $menus = DB::table('menus')
                ->select([
                    "menus.id",
                    "menus.menu",
                    "menus.link"
                ])
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'menus'  => $menus
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function roleMenus($roleId)
    {
        try {
            $menus = DB::table('role_menus')
                ->select([
                    "role_menus.*",
                    "menus.menu",
                    "menus.link"
                ])
                ->join("menus", "menus.id", "=", "role_menus.id_menus")
                ->where('role_menus.id_roles', $roleId)
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'menus'  => $menus
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }


    public function roleMenuSubmit($roleId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_menus' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $result = RoleMenu::create([
                'id_menus'      => $request->id_menus,
                'id_roles'      => $roleId,
            ]);

            $menu = DB::table('role_menus')
                ->select([
                    "role_menus.*",
                    "menus.menu",
                    "menus.link"
                ])
                ->join("menus", "menus.id", "=", "role_menus.id_menus")
                ->where('role_menus.id', $result->id)
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'menu'  => $menu,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'       => false,
                'message'       => $th->getMessage(),
                'message_type'  => 'string',
                'data'          => [],
            ], 200);
        }
    }


    public function roleMenuDestroy($roleId, $menuId)
    {
        try {
            DB::table('role_menus')
                ->where('id_roles', $roleId)
                ->where('id', $menuId)
                ->delete();

            return response()->json([
                'success'       => true,
                'message'       => 'success',
                'message_type'  => 'string',
                'data'          => [],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'       => false,
                'message'       => $th->getMessage(),
                'message_type'  => 'string',
                'data'          => [],
            ], 500);
        }
    }


    public function roleApis($roleId)
    {
        try {
            $apis = DB::table('role_api_modules')
                ->select([
                    "role_api_modules.*",
                    "api_modules.name",
                    "api_modules.method",
                    "api_modules.key",
                    "api_modules.url",
                    "api_modules.description",
                ])
                ->join("api_modules", "api_modules.id", "=", "role_api_modules.id_api_module")
                ->where('role_api_modules.id_roles', $roleId)
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'apis'  => $apis
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => $th->getMessage(),
                'data'      => [],
            ], 500);
        }
    }
}
