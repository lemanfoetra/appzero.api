<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{

    public function menus()
    {
        try {
            $menus = DB::table('role_menus')
                ->select(['menus.*'])
                ->join('menus', 'menus.id', '=', 'role_menus.id_menus')
                ->where('role_menus.id_roles', Auth::user()->id_role)
                ->where('menus.id_parrent', '0')
                ->get();

            foreach ($menus as $key => $menu) {
                $child = DB::table('role_menus')
                    ->select(['menus.*'])
                    ->join('menus', 'menus.id', '=', 'role_menus.id_menus')
                    ->where('role_menus.id_roles', Auth::user()->id_role)
                    ->where('menus.id_parrent', $menu->id)
                    ->get();

                $menus[$key]->child = $child;
            }

            return response()->json([
                'success'   => true,
                'message'   => '',
                'data'      => $menus,
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
