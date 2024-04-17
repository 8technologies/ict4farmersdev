<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CropCategory;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Pest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Profile;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;



/*
 if(isset($img_size[0]) && isset($img_size[1])){
    $width = $img_size[0];
    $heigt = $img_size[1];
}
*/

class MainController extends Controller
{
    use SendsPasswordResetEmails;

    public function index()
    {
        // get policy names and path
        $policies = \App\Models\Policy::all();
        return view('landing.index',compact('policies'));
    }

    public function market(Request  $request)
    {
        return view('metro.main.index');
    }

    public function slugSwitcher(Request  $request)
    {
        //echo "<pre>"; 
        //dd($request);
        if (
            isset($_POST['reason']) &&
            isset($_POST['product_id']) &&
            isset(
                $_POST['comment']
            )
        ) {
            $review = new ProductReview();
            $review->rating = $_POST['rating'];
            $review->reason = $_POST['reason'];
            $review->comment = $_POST['comment'];
            $review->product_id = $_POST['product_id'];
            $review->user_id =  Auth::id();

            $url = $_SERVER['REQUEST_URI'];

            if ($review->save()) {
                $errors['success'] = "Review was submitted successfully!";
                return redirect($url)
                    ->withErrors($errors)
                    ->withInput();
            } else {
                $errors['password'] = "Failed to submit review, please try again.";
                return redirect($url)
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        $seg = request()->segment(1);

        if ($seg == 'product-listing') {
            return view('metro.main.product-listing');
        }

        $profile = Profile::where('username', $seg)->first();
        if ($profile) {
            return view('main.display-profile');
        }

        $pro = Product::where('slug', $seg)->first();
        if ($pro) {
            return view('metro.main.products-details');
        }

        $pro = Product::where('id', $seg)->first();
        if ($pro) {
            return view('metro.main.products-details');
        }

        $cat = Category::where('slug', $seg)->first();
        if ($cat) {
            return view('metro.main.product-listing');
        }
        
        return abort('404');
    }

    public function password_reset(Request  $request)
    {

        if (
            isset($_POST['key']) &&
            isset($_POST['new_password'])
        ) {
            $k = trim($_POST['key']);
            $new_password = trim($_POST['new_password']);
            if (strlen($k) > 2) {
                $u = User::where('remember_token', $k)->first();
                if ($u != null) {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $u->password = $hash;
                    $u->save();

                    $_u['phone_number'] = $u->email;
                    $_u['password'] = $new_password;

                    if (Auth::attempt($_u, true)) {
                        $_SESSION['credentials'] = $_u;
                        return redirect()->intended(admin_url('/auth/login'));
                        die();
                    } else {
                        $errors['password'] = "Failed to log you in.";
                        return redirect('login')
                            ->withErrors($errors)
                            ->withInput();
                    }
                }
            }
        }

        if (isset($_POST['email'])) {
            $email_address = trim($_POST['email']);
            $u = User::where("email", $email_address)->first();
            $id = 0;
            if ($u == null) {
                $pro = Profile::where("email", $email_address)->first();
                if ($pro != null) {
                    $u = User::find($pro->user_id);
                }
            }
            if ($u == null) {
                $errors['email'] = "The email you provided does not exist on our database.
                 Check your email and try again or if you don't have account, create one now.";
                return redirect('password-reset')
                    ->withErrors($errors)
                    ->withInput();
            }

            $faker = \Faker\Factory::create();
            $u->remember_token = $faker->regexify('[A-Za-z0-9]{50}');
            $u->save();
            $url = url('password-reset?key=' . $u->remember_token);

            // the message
            $message = "Hello,\nPlease click on link below to reset your password.\n\n{$url}";
            $message = wordwrap($message, 70);

            $headers = 'From: info@goprint.ug'       . "\r\n" .
                'Reply-To: info@goprint.ug' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            if (mail($email_address, "GO-PRINT PASSWORD RESET", $message, $headers)) {
                return redirect('password-reset?success=success');
                die();
                dd("SUCCESS");
            } else {
                dd("FAILED to send email. Please try again.");
            }


            // /dd(password_hash("269435158522",PASSWORD_DEFAULT));

            if (Auth::attempt($u, true)) {

                $_SESSION['credentials'] = $u;
                return redirect()->intended(admin_url('/auth/login'));
                die();

                $errors['success'] = "Account created successfully!";
                return redirect('dashboard')
                    ->withErrors($errors)
                    ->withInput();
            } else {
                $errors['password'] = "Wrong password";
                return redirect('login')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        return view('main.password-reset');
    }

    public function login(Request  $request)
    {
        Utils::session_start();
        if (Auth::guard()->check()) {
            return redirect("/");
        }
        Utils::session_start();


        if (isset($_POST['phone_number'])) {

            $u['phone_number'] = $_POST['phone_number'];
            $u['password'] = $_POST['password'];

            // /dd(password_hash("269435158522",PASSWORD_DEFAULT));

            if (Auth::attempt($u, true)) {

                $_SESSION['credentials'] = $u;
                return redirect()->intended(admin_url('/auth/login'));
                die();

                $errors['success'] = "Account created successfully!";
                return redirect('dashboard')
                    ->withErrors($errors)
                    ->withInput();
            } else {
                $errors['password'] = "Wrong password";
                return redirect('login')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        return view('main.login');
    }

    public function register(Request  $request)
    {
        if (Auth::guard()->check()) {
            return redirect("/");
        }
        Utils::session_start();


        if (
            isset($_POST['password']) &&
            isset($_POST['password1']) &&
            isset($_POST['phone_number'])
        ) {


            if ($request->input('password') !=  $request->input('password1')) {
                $errors['password1'] = "Password don't match";
                return redirect('register')
                    ->withErrors($errors)
                    ->withInput();
            }

            if (strlen($request->input('password')) < 6) {
                $errors['password1'] = "Password too short.";
                return redirect('register')
                    ->withErrors($errors)
                    ->withInput();
            }


            $u['name'] = "";
            $u['email'] = trim(str_replace("+", "", $request->input("phone_number")));
            $u['phone_number'] = $u['email'];


            $old_user = User::where('email', $u['email'])->first();
            if ($old_user) {
                $errors['phone_number'] = "User with same email or phone number already exist.";
                return redirect('register')
                    ->withErrors($errors)
                    ->withInput();
                die();
            }

            $old_user = User::where('phone_number', $u['email'])->first();
            if ($old_user) {
                $errors['phone_number'] = "User with same email or phone number already exist.";
                return redirect('register')
                    ->withErrors($errors)
                    ->withInput();
                die();
            }

            $u['password'] = Hash::make($request->input("password"));
            $users = User::create($u);
            $pro = new Profile();
            $pro->status = 0;
            $pro->user_id = $users->id;
            $pro->save();


            $credentials['phone_number'] = $u['email'];
            $credentials['password'] = $request->input("password");


            if (Auth::attempt($credentials, true)) {
                $request->session()->regenerate();
                $_SESSION['credentials'] = $credentials;
                return redirect()->intended(admin_url('/auth/login'));
            } else {
                return redirect()->intended('login');
            }
        }
        return view('main.register');
    }

    public function reset_password_phone()
    {
        return view('metro.auth.reset_password_phone');
    }

    public function reset_password_code()
    {
        Utils::session_start();
        $user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = ((int)($_SESSION['user_id']));
        }
        $u = Administrator::find($user_id);
        if ($u == null) {
            die("Session not found.");
        }

        return view('metro.auth.reset_password_code');
    }

    public function reset_password()
    {
        Utils::session_start();
        $user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = ((int)($_SESSION['user_id']));
        }
        $u = Administrator::find($user_id);
        if ($u == null) {
            die("Session not found.");
        }

        return view('metro.auth.reset_password');
    }


    public function reset_password_phone_post(Request $r)
    {
        $phone_or_email = $r->email;
        //check if phone_or_email is an email
        $is_email = filter_var($phone_or_email, FILTER_VALIDATE_EMAIL);
        if($is_email) {

            //send password reset link
            $u = User::where('email', $phone_or_email)->first();
            if ($u == null) {
                $errors['phone_number'] = "Account with that email was not found on our database.";
                return Redirect::back()
                    ->withErrors($errors)
                    ->withInput();
            }
            $this->sendResetLinkEmail($r);
            session()->flash('success_message',"We have just sent to you an email with a password reset link.");
            return Redirect::back();

        }else{
            $phone_number = Utils::prepare_phone_number($r->email);
            $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
            if (!$phone_number_is_valid) {
                $errors['phone_number'] = "Please enter a valid phone number.";
                return Redirect::back()
                    ->withErrors($errors)
                    ->withInput();
            }
            $u =
                User::where('phone_number',  $phone_number)
                ->orWhere('username',  $phone_number)
                ->orWhere('username',  $phone_number)
                ->first();
            if ($u == null) {
                $errors['phone_number'] = "Account with that phone number was not found on our database.";
                return Redirect::back()
                    ->withErrors($errors)
                    ->withInput();
            }
    
    
            Utils::session_start();
            $_SESSION['user_id'] = $u->id;
    
            $u->verification_code = rand(1000, 9999) . "";
            $resp = Utils::send_sms([
                'to' => $phone_number,
                'message' => 'Your ICT4Farmers password reset code is ' . $u->verification_code
            ]);
    
            if (!$resp) {
                $errors['phone_number'] = "We have failed to send a verification code to your number. 
                Please contact us on +256 780 602550 and we help reset your password.";
                return Redirect::back()
                    ->withErrors($errors)
                    ->withInput();
            }
    
            $u->save();
            $errors['success'] = "We have just sent to you an SMS with a password reset code on your number {$phone_number}.";
            return redirect(url('reset-password-code'))
                ->withErrors($errors)
                ->withInput();
    
        }

    }

    // public function reset_password_code_post(Request $r)
    // {

    //     Utils::session_start();
    //     $user_id = 0;
    //     if (isset($_SESSION['user_id'])) {
    //         $user_id = ((int)($_SESSION['user_id']));
    //     }
    //     $u = Administrator::find($user_id);
    //     if ($u == null) {
    //         die("Session not found.");
    //     }



    //     $code = (trim($r->code));

    //     if ($r->code != $u->verification_code) {
    //         $errors['code'] = "Verirification code you provided did not math with the one we have sent to you.";
    //         return Redirect::back()
    //             ->withErrors($errors)
    //             ->withInput();
    //     }
    //     $_SESSION['can_set_password'] = "yes";

    //     return redirect(url('reset-password'));
    // }

    public function reset_password_post(Request $r)
    {

        Utils::session_start();
        $user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = ((int)($_SESSION['user_id']));
        }
        $u = Administrator::find($user_id);
        if ($u == null) {
            die("Session not found.");
        }

        $password_1 = (string) (trim($r->password_1));
        $password_2 = (string) (trim($r->password_2));

        if (strlen($password_1) < 4) {
            $errors['password_1'] = "Your password is too short.";
            return Redirect::back()
                ->withErrors($errors)
                ->withInput();
        }
        if ($password_1 !=  $password_2) {
            $errors['password_2'] = "Passwords did not match.";
            return Redirect::back()
                ->withErrors($errors)
                ->withInput();
        }

        $u->password = password_hash($password_1, PASSWORD_DEFAULT);
        $u->save();
        $_SESSION['success_message'] = "Password reset successfull. Now login with your new password.";
        return redirect(url('login'));
    }




    public function about()
    {
        return view('metro.about_us');
    }

    public function privacy()
    {
        return view('metro.privacy');
    }

    public function sell_fast()
    {
        return view('about.sell_fast');
    }

    public function contact()
    {
        return view('about.contact');
    }

    public function test()
    {
        return view('main.test');
    }
}
