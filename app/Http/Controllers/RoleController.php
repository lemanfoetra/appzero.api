<?php

namespace App\Http\Controllers;

use App\Models\Role;
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



    public function update(Role $role, Request $request)
    {
        try {
            $validation = [
                'name' => "required|string|max:255|unique:roles,name,{$role->id}",
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
                ->where('id', $role->id)
                ->update($data);

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'user'  => $data,
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
}
