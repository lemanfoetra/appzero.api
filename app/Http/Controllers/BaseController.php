<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class BaseController extends Controller
{

    public function menus()
    {
        try {
            $menus = $this->getListMenus();
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


    public function menuAccess()
    {
        try {
            $menus = DB::table('menus')
                ->select(['menus.id', 'menus.link'])
                ->join('role_menus', 'menus.id', '=', 'role_menus.id_menus')
                ->where('role_menus.id_roles', Auth::user()->id_role)
                ->whereNotNull('link')
                ->orderBy('menus.urutan', 'asc')
                ->get();

            foreach ($menus ?? [] as $key => $menu) {
                $access = [];
                $accessFunctions = DB::table('role_menu_accesses')
                    ->select(['access_code'])
                    ->where('id_menus', $menu->id)
                    ->where('id_roles', Auth::user()->id_role)
                    ->get();
                foreach ($accessFunctions as $acc) {
                    $access = array_merge($access, [$acc->access_code]);
                }
                $menus[$key]->access = $access;
            }

            return response()->json([
                'success'   => true,
                'message'   => 'success',
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


    private function getListMenus()
    {
        $menus = [];
        $parrents = DB::table('menus')
            ->select(['menus.id', 'menus.id_parrent', 'menus.menu', 'menus.link', 'menus.urutan', 'menus.icon'])
            ->join('role_menus', 'menus.id', '=', 'role_menus.id_menus')
            ->where('role_menus.id_roles', Auth::user()->id_role)
            ->where('menus.id_parrent', '0')
            ->orderBy('menus.urutan', 'asc')
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
            ->select(['menus.id', 'menus.id_parrent', 'menus.menu', 'menus.link', 'menus.urutan', 'menus.icon'])
            ->join('role_menus', 'menus.id', '=', 'role_menus.id_menus')
            ->where('role_menus.id_roles', Auth::user()->id_role)
            ->where('menus.id_parrent', $id_menu)
            ->orderBy('menus.urutan', 'asc')
            ->get();

        foreach ($childs as $child) {
            $level3 = DB::table('menus')
                ->select(['menus.id', 'menus.id_parrent', 'menus.menu', 'menus.link', 'menus.urutan', 'menus.icon'])
                ->join('role_menus', 'menus.id', '=', 'role_menus.id_menus')
                ->where('role_menus.id_roles', Auth::user()->id_role)
                ->where('menus.id_parrent', $child->id)
                ->orderBy('menus.urutan', 'asc')
                ->get();

            $child->childs = $level3;
            $menus = array_merge($menus, [$child]);
        }

        return $menus;
    }
}
