<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterMenuController extends Controller
{

    public function index(Request $request)
    {
        try {
            $datas = $this->getListData($request);
            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'menus' => $datas
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


    public function menus()
    {
        try {
            $roles = DB::table('menus')
                ->select([
                    'id',
                    'menu',
                ])
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


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_parrent'    => 'required',
                'menu'          => 'required|string|max:255',
                'link'          => 'required|string|max:255',
                // 'icon'          => 'required',
                'urutan'        => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $menu = Menu::create([
                'id_parrent'    => $request->id_parrent,
                'menu'          => $request->menu,
                'link'          => $request->link,
                'icon'          => $request->icon,
                'urutan'        => $request->urutan,
            ]);

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


    public function update(Menu $menu, Request $request)
    {
        try {
            $validation = [
                'id_parrent'    => 'required',
                'menu'          => 'required|string|max:255',
                'link'          => 'required|string|max:255',
                // 'icon'          => 'required',
                'urutan'        => 'required',
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
                'id_parrent'    => $request->id_parrent,
                'menu'          => $request->menu,
                'link'          => $request->link,
                'icon'          => $request->icon,
                'urutan'        => $request->urutan,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
            DB::table('menus')
                ->where('id', $menu->id)
                ->update($data);

            $menu = DB::table('menus')
                ->where('id', $menu->id)
                ->select(['id', 'id_parrent', 'menu', 'link', 'urutan', 'icon'])->first();

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
            ], 500);
        }
    }


    public function delete($menu)
    {
        try {
            DB::table('menus')
                ->where('id', $menu)
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


    public function show($menuId)
    {
        try {
            $menu = DB::table('menus')
                ->select(['*'])
                ->where('id', $menuId)
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'menu'  => $menu
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


    private function getListData($request)
    {
        $menus = [];
        $parrents = DB::table('menus')
            ->select(['id', 'id_parrent', 'menu', 'link', 'urutan'])
            ->where('id_parrent', '0')
            ->orderBy('urutan', 'asc')
            ->get();

        foreach ($parrents as $parrent) {
            $parrent            = $parrent;
            $parrent->childs  = $this->childMenus($parrent->id);
            $menus = array_merge($menus, [$parrent]);
        }

        return $menus;
    }


    private function childMenus($id_menu)
    {
        $menus = [];
        $childs = DB::table('menus')
            ->select(['id', 'id_parrent', 'menu', 'link', 'urutan'])
            ->where('id_parrent', $id_menu)
            ->orderBy('urutan', 'asc')
            ->get();

        foreach ($childs as $child) {
            $level3 = DB::table('menus')
                ->select(['id', 'id_parrent', 'menu', 'link', 'urutan'])
                ->where('id_parrent', $child->id)
                ->orderBy('urutan', 'asc')
                ->get();

            $child->childs = $level3;
            $menus = array_merge($menus, [$child]);
        }

        return $menus;
    }
}
