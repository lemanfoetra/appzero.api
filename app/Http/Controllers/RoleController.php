<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleApiModule;
use App\Models\RoleMenu;
use App\Models\RoleMenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
                ->select(["role_menus.*", "menus.menu", "menus.link", "menus.id_parrent"])
                ->join("menus", "menus.id", "=", "role_menus.id_menus")
                ->where('role_menus.id_roles', $roleId)
                ->where('menus.id_parrent', '0')
                ->get();

            // GET ACCESS FUNCTION
            foreach ($menus as $key => $menu) {
                $access = [];
                $accessFunctions = DB::table('role_menu_accesses')
                    ->select(['access_code'])
                    ->where('id_menus', $menu->id_menus)
                    ->where('id_roles', $menu->id_roles)
                    ->get();
                foreach ($accessFunctions as $acc) {
                    $access = array_merge($access, [$acc->access_code]);
                }

                $menus[$key]->access_function = $access;
            }

            // MENU LEVEL 2
            foreach ($menus as $keyLev2 => $level2) {
                $menusLev2 = DB::table('role_menus')
                    ->select(["role_menus.*", "menus.menu", "menus.link", "menus.id_parrent"])
                    ->join("menus", "menus.id", "=", "role_menus.id_menus")
                    ->where('role_menus.id_roles', $roleId)
                    ->where('menus.id_parrent', $level2->id_menus)
                    ->get();
                // GET ACCESS FUNCTION
                foreach ($menusLev2 ?? [] as $kAcL2 => $menu) {
                    $access = [];
                    $accessFunctions = DB::table('role_menu_accesses')
                        ->select(['access_code'])
                        ->where('id_menus', $menu->id_menus)
                        ->where('id_roles', $menu->id_roles)
                        ->get();
                    foreach ($accessFunctions as $acc) {
                        $access = array_merge($access, [$acc->access_code]);
                    }
                    $menusLev2[$kAcL2]->access_function = $access;
                }

                // MANU LEVEL 3
                foreach ($menusLev2 as $keyLev3 => $level3) {
                    $menusLev3 = DB::table('role_menus')
                        ->select(["role_menus.*", "menus.menu", "menus.link", "menus.id_parrent"])
                        ->join("menus", "menus.id", "=", "role_menus.id_menus")
                        ->where('role_menus.id_roles', $roleId)
                        ->where('menus.id_parrent', $level3->id_menus)
                        ->get();
                    
                        // GET ACCESS FUNCTION
                    foreach ($menusLev3 ?? [] as $kAcL3 => $menu) {
                        $access = [];
                        $accessFunctions = DB::table('role_menu_accesses')
                            ->select(['access_code'])
                            ->where('id_menus', $menu->id_menus)
                            ->where('id_roles', $menu->id_roles)
                            ->get();
                        foreach ($accessFunctions as $acc) {
                            $access = array_merge($access, [$acc->access_code]);
                        }
                        $menusLev3[$kAcL3]->access_function = $access;
                    }
                    $menusLev2[$keyLev3]->childs = $menusLev3;
                }

                $menus[$keyLev2]->childs = $menusLev2;
            }

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


    public function roleMenuApis($roleId, $menuId)
    {
        try {
            $apis = DB::table('api_modules')
                ->select([
                    "api_modules.*",
                    "menus.menu",
                    "menus.link",
                ])
                ->join("menus", "menus.id", "=", "api_modules.id_menus")
                ->where('api_modules.id_menus', $menuId)
                ->get();

            foreach ($apis as $key => $api) {
                $access = DB::table('role_api_modules')
                    ->select(["id"])
                    ->where('id_api_module', $api->id)
                    ->where('id_roles', $roleId)
                    ->first();
                if (!empty($access) && $access->id !== null) {
                    $apis[$key]->access = 'Y';
                } else {
                    $apis[$key]->access = 'N';
                }
            }

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


    public function roleMenuApisSubmit($roleId, $menuId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_api_module' => 'required|numeric',
                'access'        => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            if ($request->access == 'Y') {
                $old = DB::table('role_api_modules')
                    ->where('id_roles', $roleId)
                    ->where('id_api_module', $request->id_api_module)
                    ->first();

                if (empty($old)) {
                    RoleApiModule::create([
                        'id_api_module'      => $request->id_api_module,
                        'id_roles'      => $roleId,
                    ]);
                }
            } else if ($request->access == 'N') {
                DB::table('role_api_modules')
                    ->where('id_roles', $roleId)
                    ->where('id_api_module', $request->id_api_module)
                    ->delete();
            }

            $result = DB::table('api_modules')
                ->select([
                    "api_modules.*",
                    "menus.menu",
                    "menus.link",
                ])
                ->join("menus", "menus.id", "=", "api_modules.id_menus")
                ->where('api_modules.id', $request->id_api_module)
                ->first();

            $access = DB::table('role_api_modules')
                ->select(["id"])
                ->where('id_api_module', $result->id)
                ->where('id_roles', $roleId)
                ->first();
            if (!empty($access) && $access->id !== null) {
                $result->access = 'Y';
            } else {
                $result->access = 'N';
            }

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'api'   => $result,
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


    public function roleMenuAccessSubmit($roleId, $menuId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'access_code'        => 'required',
                'access_status'      => [
                    'required',
                    Rule::in(["Y", "N"])
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            if ($request->access_status == 'Y') {
                $old = DB::table('role_menu_accesses')
                    ->where('id_roles', $roleId)
                    ->where('id_menus', $menuId)
                    ->where('access_code', $request->access_code)
                    ->first();

                if (empty($old)) {
                    RoleMenuAccess::create([
                        'id_menus'      => $menuId,
                        'id_roles'      => $roleId,
                        "access_code"   => $request->access_code,
                    ]);
                }
            } else if ($request->access_status == 'N') {
                DB::table('role_menu_accesses')
                    ->where('id_roles', $roleId)
                    ->where('id_menus', $menuId)
                    ->where('access_code', $request->access_code)
                    ->delete();
            }

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'access'   => [
                        'id_roles'  => $roleId,
                        'id_menus'  => $menuId,
                        'access_code'   => $request->access_code,
                        'access_status' => $request->access_status,
                    ],
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
}
