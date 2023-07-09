<?php

namespace App\Http\Controllers;

use App\Models\FarmersGroup;
use App\Models\Location;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Str;


class ApiUsersController
{
    public function farmers_goups(Request $request)
    {
        return FarmersGroup::all();
    }


    public function index(Request $request)
    {
        $user_id = (int) ($request->user_id ? $request->user_id : 0);
        $per_page = isset($request->per_page) ? $request->per_page : 1000;

        if ($user_id > 0) {
            $items = User::where('id', $user_id)->paginate($per_page)->withQueryString()->items();
        } else {
            $items = User::paginate($per_page)->withQueryString()->items();
        }
        return $items;
    }

    public function verify_phone(Request $request)
    {

        $phone_number = Utils::prepare_phone_number($request->phone_number);
        $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
        if (!$phone_number_is_valid) {
            return Utils::response([
                'status' => 0,
                'message' => "Please enter a valid phone number."
            ]);
        }


        //sms verification added
        $id = (int) ($request->id ? $request->id : "0");
        $u = User::find($id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
                'data' => null
            ]);
        }



        /* if (isset($request->status)) {
            $status = trim($request->status);
            if ($status == 'verified') {
                $u->phone_number_verified = 1;
                $u->save();
                return Utils::response([
                    'status' => 1,
                    'message' => "CODE was sent to your number {$phone_number} successfully.",
                    'data' => $u
                ]);
            }
        }

        if ($u->phone_number_verified == 1) {
            return Utils::response([
                'status' => 1,
                'message' => "Your number {$phone_number} was verified successfully.",
                'data' => $u
            ]);
        }
 */
        $u->verification_code = rand(1000, 9999) . "";
        $resp = Utils::send_sms([
            'to' => $phone_number,
            'message' => 'Your ICT4Farmers verification code is ' . $u->verification_code
        ]);

        if (!$resp) {
            $u->phone_number_verified = 1;
            $u->save();
            return Utils::response([
                'status' => 1,
                'message' => "Your number {$phone_number} was verified successfully.",
                'data' => $u
            ]);
        }

        $u->phone_number = $phone_number;
        $u->phone_number_verified = 0;
        $u->save();

        return Utils::response([
            'status' => 1,
            'message' => "CODE was sent to your number {$u->phone_number} successfully.",
            'data' => $u
        ]);
    }

    public function users_profile(Request $request)
    {

        $id = (int) ($request->id ? $request->id : "0");
        $u = User::find($id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
                'data' => null
            ]);
        }

        return Utils::response([
            'status' => 1,
            'message' => "Logged successfully.",
            'data' => $u
        ]);
    }

    public function login(Request $request)
    {
        if (
            $request->email == null ||
            $request->password == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide email and password.",
                'data' => null
            ]);
        }

        $email = (string) ($request->email ? $request->email : "");
        $password = (string) ($request->password ? $request->password : "");

        $phone_number = Utils::prepare_phone_number($email);
        $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
        if (!$phone_number_is_valid) {
            return Utils::response([
                'status' => 0,
                'message' => "Please enter a valid phone number."
            ]);
        }


        $_u = User::where('phone_number', $phone_number)->get();
        $u = null;
        if (isset($_u[0])) {
            $u = $_u[0];
        }

        if ($u == null) {
            $_u = User::where('username', $phone_number)->get();
            if (isset($_u[0])) {
                $u = $_u[0];
            }
        }

        if ($u == null) {
            $_u = User::where('email', $phone_number)->get();
            if (isset($_u[0])) {
                $u = $_u[0];
            }
        }

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User account not found.",
                'data' => null
            ]);
        }


        if (!password_verify($password, $u->password)) {
            return Utils::response([
                'status' => 0,
                'message' => "Wrong password. Plese try 4321",
                'data' => null
            ]);
        }

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Account not found.",
                'data' => null
            ]);
        }


        return Utils::response([
            'status' => 1,
            'message' => "Logged successfully.",
            'data' => $u
        ]);
    }

    public function update(Request $request)
    {

        if (
            $request->email == null ||
            $request->name == null ||
            $request->user_id == null
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide Name, email and user id.",
                'data' => null
            ]);
        }

        $user_id = (int) ($request->user_id ? $request->user_id : 0);
        $email = (string) ($request->email ? $request->email : "");
        $u = User::find($user_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to find account with ID {$user_id}",
                'data' => null
            ]);
        }

        $_u = User::where('email', $email)->get();
        if (isset($_u['0'])) {
            if ($_u['0']->id != $u->id) {
                return Utils::response([
                    'status' => 0,
                    'message' => "Changes not saved because user with same email ({$user_id}) that you provided already exist.",
                    'data' => null
                ]);
            }
        }

        $u->email = $email;
        $u->email = $u->email;
        $u->username = $u->email;

        $u->name = (string) ($request->name ? $request->name : "");
        $u->username = (string) ($request->email ? $request->email : "");
        $u->company_name = (string) ($request->company_name ? $request->company_name : "");
        $u->address = (string) ($request->address ? $request->address : "");
        $u->about = (string) ($request->about ? $request->about : "");
        $u->services = (string) ($request->services ? $request->services : "");
        $u->longitude = (string) ($request->longitude ? $request->longitude : "");
        $u->latitude = (string) ($request->latitude ? $request->latitude : "");
        $u->division = (string) ($request->division ? $request->division : "");
        $u->facebook = (string) ($request->facebook ? $request->facebook : "");
        $u->twitter = (string) ($request->twitter ? $request->twitter : "");
        $u->whatsapp = (string) ($request->whatsapp ? $request->whatsapp : "");
        $u->instagram = (string) ($request->instagram ? $request->instagram : "");
        $u->category_id = (string) ($request->category_id ? $request->category_id : "");
        $u->country_id = (string) ($request->country_id ? $request->country_id : "");
        $u->region = (string) ($request->region ? $request->region : "");
        $u->sub_county = (string) ($request->sub_county ? $request->sub_county : "");
        $u->date_of_birth = (string) ($request->date_of_birth ? $request->date_of_birth : "");
        $u->gender = (string) ($request->gender ? $request->gender : "Male");
        $u->marital_status = (string) ($request->marital_status ? $request->marital_status : "");
        $u->user_role = (string) ($request->user_role ? $request->user_role : "");
        $u->experience = (string) ($request->experience ? $request->experience : "");
        $u->production_scale = (string) ($request->production_scale ? $request->production_scale : "");
        $u->access_to_credit = (string) ($request->access_to_credit ? $request->access_to_credit : "");

        $u->sector = (string) ($request->sector ? $request->sector : "");
        $u->profile_is_complete = true;


        if ($u->sub_county != null) {

            if (strlen($u->sub_county) > 0) {
                $sub_county = Location::find($u->sub_county);
                if ($sub_county != null) {
                    $u->district = $sub_county->parent;
                }
            }
        }

        unset($u->password);
        unset($u->status_comment);
        unset($u->opening_hours);
        unset($u->remember_token);
        unset($u->cover_photo);
        unset($u->youtube);
        unset($u->last_seen);
        unset($u->status);
        unset($u->linkedin);

        if (isset($_FILES)) {
            if ($_FILES != null) {
                if (count($_FILES) > 0) {
                    if (isset($_FILES['profile_pic'])) {
                        if ($_FILES['profile_pic'] != null) {
                            if (isset($_FILES['profile_pic']['tmp_name'])) {
                                $u->avatar = Utils::upload_file($_FILES['profile_pic']);
                            };
                        }
                        unset($_FILES['audio']);
                    }
                }
            }
        }


        if ($u->save()) {

            return Utils::response([
                'status' => 1,
                'message' => "Profile updated successfully.",
                'data' => $u
            ]);
        } else {

            return Utils::response([
                'status' => 0,
                'message' => "Failed to update profile.",
                'data' => $u
            ]);
        }
    }


    public function create_account(Request $request)
    {
        if ($request->name == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide a Name. {$request->name}",
                'data' => $request
            ]);
        } elseif ($request->email == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide a Phone Number. {$request->email}",
                'data' => $request
            ]);
        } elseif ($request->password == null) {
            return Utils::response([
                'status' => 0,
                'message' => "You must provide a Password. {$request->password}",
                'data' => $request
            ]);
        }

        $raw = $request->input("email");
        $phone_number = Utils::prepare_phone_number($request->input("email"));
        $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
        if (!$phone_number_is_valid) {
            return Utils::response([
                'status' => 0,
                'message' => "Please enter a valid phone number. e.g 0772 77 77 77 "
            ]);
        }

        $old_user_phone =
            User::where('phone_number',  $phone_number)
            ->orWhere('username',  $phone_number)
            ->orWhere('username',  $phone_number)
            ->first();

        if ($old_user_phone) {
            return Utils::response([
                'status' => 0,
                'message' => "An account with the same phone number {$phone_number} you provided already exists."
            ]);
        }

        $user = new User();
        $user->name = $request->input("name");
        $user->phone_number = $phone_number;
        $user->username = $phone_number;
        $user->password = Hash::make($request->input("password"));

        if ($user->save()) {
            DB::table('admin_role_users')->insert([
                'role_id' => 2,
                'user_id' => $user->id
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'message' => "Failed to created your account. Please try again."
            ]);
        }
        $user = Administrator::find($user->id);
        return Utils::response([
            'status' => 1,
            'message' => "Account created successfully.",
            'data' => $user
        ]);
    }
}
