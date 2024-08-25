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


    private function getListMenus()
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
