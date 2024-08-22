<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Login;
use App\Http\Controllers\MasterApiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [LoginController::class, 'login']);
Route::post('register', [RegisterController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('expense')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/{id}', [ExpenseController::class, 'show']);
        Route::put('/{id}', [ExpenseController::class, 'update']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('jumlah_pengeluaran_hari_ini', [DashboardController::class, 'pengeluaranHariIni']);
        Route::get('jumlah_pengeluaran_minggu_ini', [DashboardController::class, 'pengeluaranMingguIni']);
        Route::get('jumlah_pengeluaran_bulan_ini', [DashboardController::class, 'pengeluaranBulanIni']);

        Route::get('detail_pengeluaran_hari_ini', [DashboardController::class, 'detailPengeluaranHariIni']);
        Route::get('detail_pengeluaran_minggu_ini', [DashboardController::class, 'detailPengeluaranMingguIni']);
        Route::get('detail_pengeluaran_bulan_ini', [DashboardController::class, 'detailPengeluaranBulanIni']);
    });

    Route::prefix('base')->group(function () {
        Route::get('menus', [BaseController::class, 'menus'])->middleware("api.role:base_menus");
    });

    Route::prefix('user_management')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->middleware("api.role:user_management_get_list");
        Route::get('/{user}', [UserManagementController::class, 'show'])
            ->middleware("api.role:user_management_get_saved_user")
            ->where(['user' => '[0-9]+']);

        Route::get('roles', [UserManagementController::class, 'roles'])->middleware("api.role:user_management_roles");
        Route::post('/', [UserManagementController::class, 'store'])->middleware("api.role:user_management_post");
        Route::put('/{user}', [UserManagementController::class, 'update'])->middleware("api.role:user_management_put");
        Route::delete('/{user}', [UserManagementController::class, 'delete'])->middleware("api.role:user_management_delete");
    });

    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware("api.role:role_get_list");
        Route::get('/{roleId}', [RoleController::class, 'show'])
            ->middleware("api.role:role_get_saved_role")
            ->where(['roleId' => '[0-9]+']);
        Route::post('/', [RoleController::class, 'store'])->middleware("api.role:role_post");
        Route::put('/{roleId}', [RoleController::class, 'update'])->middleware("api.role:role_put");
        Route::delete('/{roleId}', [RoleController::class, 'delete'])->middleware("api.role:role_delete");

        Route::get('/menus', [RoleController::class, 'menus'])->middleware("api.role:role_all_menus");
        Route::get('/{roleId}/menus', [RoleController::class, 'roleMenus'])->middleware("api.role:role_menus")->where(['roleId' => '[0-9]+']);
        Route::post('/{roleId}/menus', [RoleController::class, 'roleMenuSubmit'])->middleware("api.role:role_menus_post")->where(['roleId' => '[0-9]+']);
        Route::delete('/{roleId}/menus/{menuId}', [RoleController::class, 'roleMenuDestroy'])
            ->middleware("api.role:role_menus_delete")
            ->where(['roleId' => '[0-9]+', 'menuId' => '[0-9]+']);
        Route::post('/{roleId}/menus/{menuId}/access', [RoleController::class, 'roleMenuAccessSubmit'])
            ->middleware("api.role:role_menus_access_post")
            ->where(['roleId' => '[0-9]+', 'menuId' => '[0-9]+']);

        Route::get('/{roleId}/menus/{menuId}/apis', [RoleController::class, 'roleMenuApis'])
            ->middleware("api.role:role_menu_apis")
            ->where(['roleId' => '[0-9]+'], ['menuId' => '[0-9]+']);
        Route::post('/{roleId}/menus/{menuId}/apis', [RoleController::class, 'roleMenuApisSubmit'])
            ->middleware("api.role:role_menu_apis_post")
            ->where(['roleId' => '[0-9]+'], ['menuId' => '[0-9]+']);
    });


    Route::prefix('master_api')->group(function () {
        Route::get('/', [MasterApiController::class, 'index'])->middleware("api.role:master_api_get_list");
        Route::get('/{api}', [MasterApiController::class, 'show'])
            ->middleware("api.role:master_api_get_saved_user")
            ->where(['api' => '[0-9]+']);

        Route::get('/menus', [MasterApiController::class, 'menus'])->middleware("api.role:master_api_menus");
        Route::post('/', [MasterApiController::class, 'store'])->middleware("api.role:master_api_post");
        Route::put('/{api}', [MasterApiController::class, 'update'])->middleware("api.role:master_api_put")->where(['api' => '[0-9]+']);;
        Route::delete('/{api}', [MasterApiController::class, 'delete'])->middleware("api.role:master_api_delete")->where(['api' => '[0-9]+']);;
    });
});
