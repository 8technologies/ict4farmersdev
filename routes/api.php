<?php

use App\Http\Controllers\ApiChatsController;
use App\Http\Controllers\ApiProductsController;
use App\Http\Controllers\ApiShopController;
use App\Http\Controllers\ApiUsersController;
use App\Models\CropCategory;
use App\Models\Pest;
use App\Models\Question;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; //new staff


use App\Http\Controllers\CallCenter\NewCallCenterController;


Route::post('products', [ApiProductsController::class, 'create']);
Route::post('gardens', [ApiProductsController::class, 'create_garden']);
Route::post('farms', [ApiProductsController::class, 'create_farm']);

Route::post('garden-activities', [ApiProductsController::class, 'garden_activities_create']);
Route::delete('garden-activities', [ApiProductsController::class, 'garden_activities_delete']);
Route::post('pest-cases', [ApiProductsController::class, 'pest_cases_create']);
Route::post('questions', [ApiProductsController::class, 'question_create']);
Route::post('garden-production-record', [ApiProductsController::class, 'garden_production_record_create']);
Route::post('workers', [ApiProductsController::class, 'workers_create']);
Route::post('financial-records', [ApiProductsController::class, 'financial_records_create']);
Route::get('workers', [ApiProductsController::class, 'workers']);
Route::get('wizard-items', [ApiProductsController::class, 'wizard_items']);
Route::get('app-version', function () {
    return [[
        'id' => 1,
        'version' => '6', //
    ]];
});

Route::get('farms', [ApiProductsController::class, 'farms']);
Route::get('gardens', [ApiProductsController::class, 'gardens']);
Route::get('garden-activities', [ApiProductsController::class, 'garden_activities']);
Route::get('garden-production-record', [ApiProductsController::class, 'get_garden_production_record']);
Route::get('financial-records', [ApiProductsController::class, 'get_financial_records']);

Route::get('crop-categories', function () {
    return CropCategory::all();
});

Route::get('users-1', function () {
    $users = Administrator::all();

    return $users->pluck('name', 'id');
});

Route::get('pests', function () {
    return Pest::all();
});
Route::get('questions', function () {
    return Question::all();
});

Route::get('farmers-goups', [ApiUsersController::class, 'farmers_goups']);

Route::post('upload-temp-file', [ApiProductsController::class, 'upload_temp_file']);
Route::post('products', [ApiProductsController::class, 'create']);
Route::get('upload', [ApiProductsController::class, 'upload']);
Route::get('products', [ApiProductsController::class, 'index']);
Route::post('delete-product', [ApiProductsController::class, 'delete']);
Route::get('banners', [ApiProductsController::class, 'banners']);
Route::get('categories', [ApiProductsController::class, 'categories']);
Route::get('locations', [ApiProductsController::class, 'locations']);

Route::post('get-chats', [ApiChatsController::class, 'index']);
Route::post('chats', [ApiChatsController::class, 'send_message']);
Route::get('sms', [ApiChatsController::class, 'send_sms']);
Route::post('threads', [ApiChatsController::class, 'threads']);


//User related end points
Route::get('users', [ApiUsersController::class, 'index']);
Route::post('users-update', [ApiUsersController::class, 'update']);
Route::post('users-login', [ApiUsersController::class, 'login']);
Route::get('users-profile', [ApiUsersController::class, 'users_profile']);
Route::post('verify-phone', [ApiUsersController::class, 'verify_phone']);
Route::post('users', [ApiUsersController::class, 'create_account']);

Route::get('posts', [ApiProductsController::class, 'posts']);
Route::get('post-categories', [ApiProductsController::class, 'post_categories']);
Route::post('posts', [ApiProductsController::class, 'create_post']);
//Route::post('git', [ApiProductsController::class, 'create_git_post']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user(); //simple love
});

Route::post('call_center_voice', [NewCallCenterController::class, 'call_center_voice']);
Route::get('calls', [NewCallCenterController::class, 'call_center_voice']);
Route::post('calls', [NewCallCenterController::class, 'call_center_voice']);
Route::match(['get', 'post'], 'calls', [NewCallCenterController::class, 'call_center_voice']);


Route::POST("product-create", [ApiShopController::class, "product_create"]);//==>2
Route::get('products', [ApiShopController::class, 'products']);//==>1
Route::post('products-delete', [ApiShopController::class, 'products_delete']);//==>4
Route::post('chat-send', [ApiShopController::class, 'chat_send']);//==>5
Route::post('chat-mark-as-read', [ApiShopController::class, 'chat_mark_as_read']);//==>8
Route::get('chat-heads', [ApiShopController::class, 'chat_heads']);//==>6
Route::get('chat-messages', [ApiShopController::class, 'chat_messages']);//==>7


Route::get('ajax', function (Request $r) {

    $_model = trim($r->get('model'));

    if (strlen($_model) < 2) {
        return [
            'data' => []
        ];
    }

    $model = "App\Models\\" . $_model;
    $search_by_1 = trim($r->get('search_by_1'));
    $search_by_2 = trim($r->get('search_by_2'));

    $q = trim($r->get('q'));
    if (strlen($q) < 1) {
        return [
            'data' => []
        ];
    }
    $res_1 = $model::where(
        $search_by_1,
        'like',
        "%$q%"
    )
        ->where([])
        ->limit(20)->get();
    $res_2 = [];

    if ((count($res_1) < 20) && (strlen($search_by_2) > 1)) {
        $res_2 = $model::where(
            $search_by_2,
            'like',
            "%$q%"
        )
            ->where([])
            ->limit(20)->get();
    }

    $data = [];
    foreach ($res_1 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }
    foreach ($res_2 as $key => $v) {
        $name = "";
        if (isset($v->name)) {
            $name = " - " . $v->name;
        }
        $data[] = [
            'id' => $v->id,
            'text' => "#$v->id" . $name
        ];
    }

    return [
        'data' => $data
    ];
});
