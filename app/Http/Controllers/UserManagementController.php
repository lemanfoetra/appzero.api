<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{

    public function index(Request $request)
    {
        try {
            $uqery = DB::table('users')
                ->select([
                    'id', 'id_role', 'name', 'email', 'created_at', 'updated_at'
                ]);

            if ($request->limit != null) {
                $uqery->limit($request->limit);
            } else {
                $uqery->limit(100);
            }

            if ($request->offset != null) {
                $uqery->offset($request->offset);
            }

            $users = $uqery->orderBy('updated_at', 'DESC')
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => '',
                'data'      => $users,
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
                'id_role' => 'required|numeric',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $user = User::create([
                'id_role'   => $request->id_role,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'user'  => $user,
                    'token' => 'Bearer ' . $token,
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


    public function update(User $user, Request $request)
    {
        try {
            $validation = [
                'id_role' => 'required|numeric',
                'name' => 'required|string|max:255',
                'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
            ];

            if (isset($request->password)) {
                $validation = array_merge($validation, [
                    'password' => 'required|string|min:8|confirmed'
                ]);
            }
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
                'id_role'   => $request->id_role,
                'name'      => $request->name,
                'email'     => $request->email,
            ];
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }

            DB::table('users')
                ->where('id', $user->id)
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


    public function delete($user)
    {
        try {
            DB::table('users')
                ->where('id', $user)
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


    public function roles()
    {
        try {
            $roles = DB::table('roles')
                ->select(['id', 'name'])
                ->get();

            return response()->json([
                'success'   => true,
                'message'   => "success",
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
}
