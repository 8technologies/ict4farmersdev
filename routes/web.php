<?php


use App\Http\Controllers\Dashboard;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardCategoriesControler;
use App\Http\Controllers\DashboardLocationControler;
use App\Http\Controllers\DashboardMenuControler;
use App\Http\Controllers\DashboardChatsControler;
use App\Http\Controllers\DashboardProductsControler;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BannersController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Models\Gen;
use App\Models\Product;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Support\Facades\Artisan;

Route::get('/migrate', function () {
    
    

    $i = 1;
    foreach (Product::all() as $key => $p) {
        echo $i . ". " . $p->local_id . "<br>";

        $i++;
        if ($i > 1000) {
            die();
        }
    } 
    echo "Migrating...<hr>";
    try {
        Artisan::call('migrate');
        $message = Artisan::output();
        dd($message);
    } catch (\Throwable $th) {
        dd($th);
    }
    die();
});
Route::get('/mail-test', function () {
    $mail = 'mubahood360@gmail.com';
    $u = User::where('email', $mail)->first();
    if ($u == null) {
        die("User not found");
    }
    try {
        $u->send_password_reset();
    } catch (\Throwable $th) {
        dd($th->getMessage());
    }
    die("success: from " . env('MAIL_FROM_ADDRESS'));

    //current link
    $link = url()->current();
    //check if link conats app2
    $hasApp2 = false;
    if (strpos($link, 'app2') !== false) {
        $hasApp2 = true;
    } else {
        $hasApp2 = false;
    }
    if ($hasApp2) {
        //redirect to https://app.unffeict4farmers.org/market
        header("Location: https://app.unffeict4farmers.org/market");
        die();
    }
    die("Mail test");
    Utils::send_sms([
        'to' => '+256783204665',
        'message' => '$sms_to_admin'
    ]);
    $data['body'] = 'Simple message';
    //$data['view'] = 'mails/mail-1';
    $data['data'] = $data['body'];
    $data['name'] = 'Muhindo mubaraka';
    $data['email'] = [
        'tukundanen@yahoo.com',
        'mubs0x@gmail.com',
        'isaac@8technologies.net',
    ];
    $data['subject'] = "Some title " . env('APP_NAME');
    try {
        Utils::mail_sender($data);
        //$this->email_sent = 'Yes';
        //$this->save();
        die("successfull sent");
    } catch (\Throwable $th) {
        //$this->email_sent = 'Failed';
        //$this->save();
        dd($th->getMessage());
    }
    die("");
});
Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("gen");

//process thumnails
Route::get('/process-thumbnails', function () {
    $images = \App\Models\Image::where('thumbnail', null)->orwhere('thumbnail', '')->get();
    foreach ($images as $image) {
        $image->processThumbnail();
        echo $image->id . " done<br>";
    }

    $producs = \App\Models\Product::where('thumbnail', null)->orwhere('thumbnail', '')->get();
    foreach ($producs as $product) {
        $product->processThumbnail();
        echo $product->id . " done<br>";
    }
    return "done";
});

Route::resource('dashboard/users', UsersController::class)->middleware(Authenticate::class);
Route::resource('dashboard/banners', BannersController::class)->middleware(Authenticate::class);

Route::get('/', [MainController::class, 'index']);
Route::get('/market', [MainController::class, 'market']);
Route::get('/inquiry', [MainController::class, 'inquiry']);
Route::post('/inquiry', [MainController::class, 'inquiry_post']);

Route::get('/banner/{id}', [MainController::class, 'index']);
Route::get('dashboard/profile', [UsersController::class, 'edit'])->middleware(Authenticate::class);

Route::get('/register', [AuthController::class, 'register'])->name("register")
    ->middleware(RedirectIfAuthenticated::class);

Route::post('/register', [AuthController::class, 'store']);
Route::post('dashboard/profile', [AuthController::class, 'update_profile']);
Route::get('/login', [AuthController::class, 'login'])->name("login");
Route::post('/login', [AuthController::class, 'do_login']);
Route::resource('/dashboard/categories', DashboardCategoriesControler::class)->middleware(Authenticate::class);
Route::resource('/dashboard/locations', DashboardLocationControler::class)->middleware(Authenticate::class);


Route::get('/dashboard', [DashboardProductsControler::class, 'index'])->name("dashboard")->middleware(Authenticate::class);
Route::resource('/dashboard/menu', DashboardMenuControler::class)->middleware(Authenticate::class);
Route::resource('/dashboard/chats', DashboardChatsControler::class)->middleware(Authenticate::class);
Route::resource('/dashboard/products', DashboardProductsControler::class)->middleware(Authenticate::class);

Route::get('/about', [MainController::class, 'about']);
Route::get('/privacy', [MainController::class, 'privacy']);
Route::get('/privacy-policy', [MainController::class, 'privacy']);
Route::get('/privacy-policy.html', [MainController::class, 'privacy']);
Route::get('/privacy.html', [MainController::class, 'privacy']);
//Route::get('/register', [MainController::class, 'register'])->name("register");
Route::match(['get', 'post'], '/password-reset', [MainController::class, 'password_reset'])->name("password-reset");
//Route::match(['get', 'post'], '/login', [MainController::class, 'login'])->name("login");
//Route::post('/register', [MainController::class, 'register'])->name("register");

Route::get('/complete-profile-request', [Dashboard::class, 'complete_profile_request'])->name("complete_profile_request")->middleware(Authenticate::class);
Route::get('/membership', [Dashboard::class, 'membership'])->name("membership")->middleware(Authenticate::class);
Route::get('/favourites', [Dashboard::class, 'favourites'])->name("favourites");
Route::match(['get', 'post'], '/post-ad', [Dashboard::class, 'postAdCategpryPick'])->name("post-ad")->middleware(Authenticate::class);
Route::get('/post-ad/{id}', [Dashboard::class, 'postAd'])->middleware(Authenticate::class);
Route::match(['get', 'post'], '/profile-edit/{id}', [Dashboard::class, 'profileEdit'])->name("profile-edit");
Route::get('/profile', [Dashboard::class, 'profile'])->middleware(Authenticate::class);
Route::get('/logout', [Dashboard::class, 'logout'])->middleware(Authenticate::class);
Route::match(['get', 'post'], '/messages/', [Dashboard::class, 'messages'])->name("messages")->middleware(Authenticate::class);
Route::match(['get', 'post'], '/messages/{thread}', [Dashboard::class, 'messages'])->name("messages-thread")->middleware(Authenticate::class);
Route::match(['get', 'post'], 'test/{id}', [MainController::class, 'test']);
Route::match(['get', 'post'], 'test', ['before' => 'csrf', MainController::class, 'test']);
Route::get('/sell-fast', [MainController::class, 'sell_fast']);
Route::get('/contact', [MainController::class, 'contact']);

//Reset passwords
Route::get('/reset-password-phone', [MainController::class, 'reset_password_phone']);
Route::post('/reset-password-phone', [MainController::class, 'reset_password_phone_post']);
// Route::get('/reset-password-code', [MainController::class, 'reset_password_code']);
// Route::post('/reset-password-code', [MainController::class, 'reset_password_code_post']);

Route::get('configs-setup', function () {
    Artisan::call('optimize:clear');
    Artisan::call('storage:link');
});

Route::match(['get', 'post'], '/{id}', [MainController::class, 'slugSwitcher']);


Route::post('call_center_voice', [CallCenterController::class, 'call_center_voice']);

Route::get('password/reset/{token}', function ($token) {
    return view('metro.auth.reset_password', ['token' => $token]);
})->name('password.reset');
Route::post('password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset');
