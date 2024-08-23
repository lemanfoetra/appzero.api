<?php

namespace App\Http\Controllers;

use App\Models\ApiModule;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterApiController extends Controller
{

    public function index(Request $request)
    {
        try {
            $datas = $this->getListData($request);
            $totalDatas = $this->getCountData($request);

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => $datas,
                'total'     => $totalDatas,
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
                    'id_parrent',
                    'menu',
                    'link',
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
                'name'      => 'required|string|max:255',
                'method'    => 'required|string|max:10',
                'key'       => 'required|string|max:255|unique:api_modules',
                'url'           => 'required|max:255',
                'description'   => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success'       => false,
                    'message'       => $validator->errors(),
                    'message_type'  => 'array',
                    'data'          => [],
                ], 422);
            }

            $api = ApiModule::create([
                'id_menus'      => $request->id_menus,
                'name'          => $request->name,
                'method'        => $request->method,
                'key'           => $request->key,
                'url'           => $request->url,
                'query_param'   => $request->query_param,
                'description'   => $request->description,
                'header'        => $request->header,
                'body'          => $request->body,
                'response'      => $request->response,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            $menu = DB::table('menus')
                ->select(['menu'])
                ->where('id', $api->id_menus)
                ->first();

            $api->menu = ($menu->menu ?? null);

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'api'  => $api,
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


    public function update(ApiModule $api, Request $request)
    {
        try {
            $validation = [
                'name'          => 'required|string|max:255',
                'method'        => 'required|string|max:10',
                'key'           => "required|string|max:255|unique:api_modules,key,{$api->id}",
                'url'           => 'required|max:255',
                'description'   => 'required',
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
                'id_menus'      => $request->id_menus,
                'name'          => $request->name,
                'method'        => $request->method,
                'key'           => $request->key,
                'url'           => $request->url,
                'query_param'   => $request->query_param,
                'description'   => $request->description,
                'header'        => $request->header,
                'body'          => $request->body,
                'response'      => $request->response,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
            DB::table('api_modules')
                ->where('id', $api->id)
                ->update($data);

            $api = DB::table('api_modules')
                ->where('id', $api->id)
                ->select([
                    'id',
                    'id_menus',
                    'name',
                    'method',
                    'key',
                    'url',
                    'description',
                    'updated_at',
                    DB::raw("(SELECT B.menu FROM menus B WHERE B.id = api_modules.id_menus ) AS menu"),
                ])->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'api'  => $api,
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


    public function delete($api)
    {
        try {
            DB::table('api_modules')
                ->where('id', $api)
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


    public function show($api)
    {
        try {
            $api = DB::table('api_modules')
                ->select([
                    'id',
                    'id_menus',
                    'name',
                    'method',
                    'key',
                    'url',
                    'description',
                    'updated_at',
                    DB::raw("(SELECT B.menu FROM menus B WHERE B.id = api_modules.id_menus ) AS menu"),
                ])
                ->where('id', $api)
                ->first();

            return response()->json([
                'success'   => true,
                'message'   => 'success',
                'data'      => [
                    'api'  => $api
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
        $uqery = DB::table('api_modules')
            ->select([
                'id',
                'id_menus',
                'name',
                'method',
                'key',
                'url',
                'description',
                'updated_at',
                DB::raw("(SELECT B.menu FROM menus B WHERE B.id = api_modules.id_menus ) AS menu"),
            ]);

        if ($request->id_menus != null) {
            $uqery->where('id_menus', $request->id_menus);
        }

        if ($request->limit != null) {
            $uqery->limit($request->limit);
        } else {
            $uqery->limit(100);
        }
        if ($request->offset != null) {
            $uqery->offset($request->offset);
        }

        // SEARCH
        if ($request->search != null) {
            $search = $request->search;
            $uqery->where(function (Builder $builder) use ($search) {
                $builder->orWhere('name',  "LIKE", '%' . $search . '%')
                    ->orWhere('key', "LIKE", '%' . $search . '%')
                    ->orWhere('description', "LIKE", '%' . $search . '%')
                    ->orWhere('method', "LIKE", '%' . $search . '%');
            });
        }

        return $uqery->orderBy('id_menus', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
    }


    private function getCountData($request)
    {
        $uqery = DB::table('api_modules')
            ->select([
                DB::raw("COUNT(id) AS total"),
            ]);

        if ($request->id_menus != null) {
            $uqery->where('id_menus', $request->id_menus);
        }

        // SEARCH
        if ($request->search != null) {
            $search = $request->search;
            $uqery->where(function (Builder $builder) use ($search) {
                $builder->orWhere('name',  "LIKE", '%' . $search . '%')
                    ->orWhere('key', "LIKE", '%' . $search . '%')
                    ->orWhere('description', "LIKE", '%' . $search . '%')
                    ->orWhere('method', "LIKE", '%' . $search . '%');
            });
        }
        return $uqery->first()->total ?? 0;
    }
}
