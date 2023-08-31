<?php

namespace App\Models;

use App\Models\WizardItem as ModelsWizardItem;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use GuzzleHttp\Client;
use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Else_;
use Zebra_Image;

use function PHPUnit\Framework\fileExists;

class Utils
{

    public static function get_user_id($request = null)
    {
        if ($request == null) {
            return 0;
        }
        $u_id = (int)($request->user);
        if ($u_id > 0) {
            return $u_id;
        }
        $u_id = (int)($request->user_id);
        if ($u_id > 0) {
            return $u_id;
        }

        $header = (int)($request->user);
        if ($header > 0) {
            $header = (int)($request->user);
        }
        $header = (int)($request->user_id);
        if ($header > 0) {
            $header = (int)($request->user_id);
        }

        
        $header = (int)($request->header('user'));
        if ($header > 0) {
            $header = (int)($request->user);
        }
        $header = (int)($request->header('user_id'));
        if ($header > 0) {
            $header = (int)($request->user);
        }

        $header = (int)($request->header('user-id'));
        if ($header > 0) {
            $header = (int)($request->user);
        }
        if ($header < 1) {
            return 0;
        }
        return $header;
    }


    
    public static function upload_images_1($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $root_path = $_SERVER['DOCUMENT_ROOT'];

                $destination = $root_path.'/ict4farmers/public/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }


    

    public static function prepare_calendar_events($administrator_id)
    {
        $events = [];
        $activities = GardenActivity::where(['administrator_id' => $administrator_id])
            ->orWhere(['person_responsible' => $administrator_id])
            ->get();


        foreach ($activities as $act) {
            //$ev['display'] = 'list-item';
            $ev['title'] = $act->name;
            $ev['start'] = Carbon::parse($act->due_date)->format('Y-m-d');
            $details = "<b>Description:</b> " . $act->details . '<br>';
            $details .= "<b>Enterprise:</b> " . $act->enterprise->name . '<br>';
            $details .= "<b>Due to:</b> " . $ev['start'] . '<br>';


            $ev['is_done'] = $act->is_done;
            if ($act->is_done == 1 || $act->is_done == true) {
                $ev['is_done'] = 1;
                $ev['classNames'] = ['bg-success', 'border-success', 'text-white'];

                if ($act->done_status == 1 || $act->done_status == true) {
                    $details .= "<b>Activity status:</b> Done<br>";
                } else {
                    $details .= "<b>Activity status:</b> Not Done (Missed)<br>";
                }
            } else {
                $ev['is_done'] = 0;
                $details .= "<b>Activity status:</b>Pending<br>";
                $ev['classNames'] = ['bg-danger', 'border-danger', 'text-white'];
            }



            $details .= "<b>Status remarks:</b> " . $act->done_details . '<br>';
            $details .= "<b>Person responsible:</b> " . $act->assigned_to->name . '<br>';

            $ev['details'] = $details;
            $ev['administrator_id'] = $act->administrator_id;
            $ev['done_status'] = $act->done_status;
            $ev['done_details'] = $act->done_details;
            $ev['garden_id'] = $act->garden_id;
            $ev['activity_id'] = $act->id;
            $ev['id'] = count($events);
            $ev['person_responsible'] = $act->person_responsible;
            $ev['type'] = 'Scheduled activity';

            //$ev['textColor'] = 'red';

            $events[] = $ev;
        }


        return $events;
    }
    public static function is_wizard_done($user_id)
    {
        $u = User::find($user_id);
        if ($u == null) {
            return false;
        }

        if ($u->phone_number_verified != 1) {
            $u->phone_number_verified =  1;
            $u->save();
        }

        if ($u->completed_wizard == 1) {
            return true;
        }

        $done = true;

        foreach (Utils::get_wizard_actions($user_id) as $v) {
            if (($v->is_done != 1) && $v->mandatory == 1) {
                $done = false;
            }
        }
        return $done;
    }
    public static function get_wizard_actions($user_id)
    {
        $u = Administrator::find($user_id);
        if ($u == null) {
            return [];
        }

        if ($u->phone_number_verified != 1) {
            //return [];
        }
        $items = [];

        ################################################
        $item  = new WizardItem();

        if ($u->profile_is_complete) {
            $item->sub_title = 'Done';
            $item->is_done = 1;
        } else {
            $item->is_done = 0;
            $item->sub_title = 'Your profile is incomplete';
        }
        $item->id = 1;
        $item->mandatory = 1;
        $item->title = 'Complete your profile';
        $item->action_text = 'COMPLETE MY PROFILE';
        $item->screen = 'AccountEdit';
        $item->link = admin_url('/auth/setting');
        $item->description = 'After you have registered successfully created your account, it’s important to complete your profile from the “my profile” section so the system can understand what you really need to use it for and customize itself for you. From here you are able to profile your personal information or rest them.
        <br><br>Press the <b>"COMPLETE MY PROFILE"</b> button below to proceed.';
        $items[] = $item;
        ################################################


        ################################################
        $farms = Farm::where([
            'administrator_id' => $user_id
        ])->get();
        $item  = new WizardItem();
        if (count($farms) < 1) {
            $item->is_done = 0;
            $item->sub_title = "You have no any farm registered.";
        } else {
            $item->sub_title = 'You have ' . count($farms) . " farms.";
            $item->is_done = 1;
        }
        $item->id = 2;
        $item->mandatory = 1;
        $item->title = 'Add your first farm';
        $item->action_text = "ADD FARM";
        $item->screen = 'FarmCreateScreen';
        $item->link = admin_url('farms/create');
        $item->description = 'In manage your farms using this system,
        you need to have add your farms first. Farm that you add into the system farm will give you access to enterprises. 
        A farm will have many enterprises or call them projects for example your farm can have poultry, fishing and cattle raring.
        <br><br>When registering your farm its highly recommend to do so on ground in order for the application to pick your graphical location, this will help the application taller services that are near you for convince. ';
        $item->is_done = 1;
        $items[] = $item;
        ################################################

        ################################################
        // $enterprises = Garden::where([
        //     'administrator_id' => $user_id
        // ])->get();
        //check if the user has any enterprise relationship
        $enterprises = User::find(Auth::user()->id)->enterprises;
        $item  = new WizardItem();
        if (count($enterprises) < 1) {
            $item->is_done = 0;
            $item->sub_title = "You have no any enterprise registered.";
        } else {
            $item->sub_title = 'You have ' . count($enterprises) . " enterprises.";
            $item->is_done = 1;
        }
        $item->id = 3;
        $item->mandatory = 1;
        $item->title = 'Add your first enterprise';
        $item->action_text = "ADD ENTERPRISE";
        $item->screen = 'GardenCreateScreen';
        $item->link = admin_url('gardens/create');
        $item->description = 'An enterprise is the farming venture/project that you are carrying on your farm. 
        For example, your poultry project, your garden, your cattle herd, among others. 
        <br><br>Press the <b>CREATE ENTERPRISE BUTTON</b>.
        To go ahead and add your first enterprise!';
        $item->is_done = 1;
        $items[] = $item;
        ################################################

        ################################################
        $users = User::where([
            'owner_id' => $user_id
        ])->get();

        $item  = new WizardItem();
        if (count($users) < 1) {
            $item->is_done = 0;
            $item->sub_title = "You have no added any worker.";
        } else {
            $item->sub_title = 'You have ' . count($users) . " worker.";
            $item->is_done = 1;
        }
        $item->id = 4;
        $item->mandatory = 1;
        $item->title = 'Worker creation';
        $item->action_text = "CREATE WORKER";
        $item->link = admin_url('my-workers/create');
        $item->screen = 'WorkerCreateScreen';
        $item->description = 'Worker is a person who does a specified type of work at your enterprises.';
        $item->is_done = 1;
        $items[] = $item;
        ################################################

        ################################################
        $activities = GardenActivity::where([
            'administrator_id' => $user_id
        ])->orWhere([
            'person_responsible' => $user_id
        ])->get();

        $item  = new WizardItem();
        if (count($activities) < 1) {
            $item->is_done = 0;
            $item->sub_title = "You have not scheduled any activity.";
        } else {
            $item->sub_title = 'You scheduled ' . count($activities) . " activities.";
            $item->is_done = 1;
        }
        $item->id = 5;
        $item->mandatory = 1;
        $item->title = 'Activity scheduling';
        $item->action_text = "SCHEDULE ACTIVITY";
        $item->screen = 'GardenActivityCreateScreen';
        $item->link = admin_url('garden-activities/create');
        $item->description = 'Use this activity scheduling to schedule all your enterprise activities in one place.';
        $item->is_done = 1;
        $items[] = $item;
        ################################################


        ################################################
        $products = Product::where([
            'user_id' => $user_id
        ])->get();

        $item  = new WizardItem();
        if (count($products) < 1) {
            $item->is_done = 0;
            $item->sub_title = "You have not posted any product.";
        } else {
            $item->sub_title = 'You posted ' . count($products) . " products.";
            $item->is_done = 1;
        }
        $item->mandatory = 1;
        $item->id = 6;
        $item->title = 'Sell your farm products';
        $item->action_text = "POST PRODUCT";
        $item->screen = 'ProductAddForm';
        $item->description = 'Buy and sell your farm products and services using ICT4farmers platform.';
        $item->is_done = 1;
        $items[] = $item;
        ################################################


        ################################################
        $products = Product::where([
            'user_id' => $user_id
        ])->get();

        $item  = new WizardItem();
        $item->is_done = 0;
        $item->title = 'Docs';
        $item->mandatory = 1;
        $item->sub_title = "Learn how to to use ICT4Farmers system.";
        $item->id = 7;
        $item->action_text = "LEARN";
        $item->screen = '';
        $item->description = 'Learn how to to use ICT4Farmers system.';
        $item->is_done = 1;
        $items[] = $item;
        ################################################



        return $items;
    }

    public static function is_profile_complete($p)
    {
        if (
            ($p->profile_is_complete !=  1)
        ) {
            return false;
        }
        return true;
    }

    public static function check_roles($u)
    {
        if ($u == null) {
            return false;
        }

        $roles = DB::table('admin_role_users')->where([
            'role_id' => 2,
            'user_id' => $u->id
        ])->get();

        if (count($roles) < 1) {
            DB::table('admin_role_users')->insert([
                'role_id' => 2,
                'user_id' => $u->id
            ]);
        }
    }
    public static function phone_number_is_valid($phone_number)
    {
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {

        if (strlen($phone_number) == 14) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = str_replace("256", "", $phone_number);
        }


        if (strlen($phone_number) > 11) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (strlen($phone_number) == 10) {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }


        if (strlen($phone_number) != 9) {
            return "";
        }

        $phone_number = "+256" . $phone_number;
        return $phone_number;
    }
    public static function send_sms($data)
    {

        if (
            !isset($data['to'])
        ) {
            return false;
        }

        $data['to'] = Utils::prepare_phone_number($data['to']);
        $phone_number_is_valid = Utils::phone_number_is_valid($data['to']);
        if (!$phone_number_is_valid) {
            return false;
        }


        $client = new Client();
        $response = $client->post('https://api.africastalking.com/version1/messaging', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'apiKey' => '88afa91724fdcd5150d211b496cd1ad1fa56f8d4c88a1293dc79cedce12636ff',
                'username' => 'farmerict',
            ],
            'form_params' => [
                'apiKey' => '88afa91724fdcd5150d211b496cd1ad1fa56f8d4c88a1293dc79cedce12636ff',
                'username' => 'farmerict',
                'to' => $data['to'],
                'message' => $data['message'],
            ],
        ]);



        $resp = json_decode($response->getBody(), true);
        if (isset($resp['SMSMessageData'])) {
            if (isset($resp['SMSMessageData']['Recipients'])) {
                if (isset($resp['SMSMessageData']['Recipients'][0])) {
                    $d = $resp['SMSMessageData']['Recipients'][0];
                    $statusCode = ((int)($d['statusCode']));
                    if ($statusCode < 300) {
                        return true;
                    }
                }
            }
        }

        return false;
    }



    public static function login_user($data)
    {
    }
    public static function get_locations()
    {
        $locations = [];

        $countries = Country::all();


        foreach ($countries as $key => $value) {
            $value->parent_id = 0;
            $value->type = 'main_location';
            $locations[] = $value;
        }

        $cities = City::all();


        foreach ($cities as $key => $value) {
            $value->parent_id = $value->country_id;
            $value->type = 'sub_location';
            $locations[] = $value;
        }


        return $locations;
    }

    public static function session_start()
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function response($data = [])
    {
        $resp['status'] = "1";
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status'] . "";
        }
        if (isset($data['message'])) {
            $resp['message'] = $data['message'];
        }
        if (isset($data['data'])) {
            $resp['data'] = $data['data'];
        }
        return $resp;
    }

    public static function tell_status($status)
    {
        if (!$status)
            return '<span class="badge badge-info">Not complete</span>';
        if ($status == 0)
            return '<span class="badge badge-info">Not complete</span>';
        if ($status == 1)
            return '<span class="badge badge-primary">Accepted</span>';
        if ($status == 2)
            return '<span class="badge badge-warning">Halted</span>';
        if ($status == 3)
            return '<span class="badge badge-danger">Rejected</span>';
        if ($status == 4)
            return '<span class="badge badge-danger">Pending for verification</span>';
        else
            return '<span class="badge badge-danger">Rejected</span>';
    }

    public static function show_response($status = 0, $code = 0, $body = "")
    {
        $d['status'] = $status;
        $d['code'] = $code;
        $d['body'] = $body;
        print_r(json_encode($d));
        die();
    }
    public static function get_chat_threads($user_id)
    {

        $threads = Chat::where(
            "sender",
            $user_id
        )
            ->orWhere('receiver', $user_id)
            ->orderByDesc('id')
            ->get();

        $done_ids = array();
        $ready_threads = array();
        foreach ($threads as $key => $value) {
            if (in_array($value->thread, $done_ids)) {
                continue;
            }
            $done_ids[] = $value->thread;
            $ready_threads[] = $value;
        }
        return $ready_threads;
    }


    public static function send_message($msg = [])
    {
        $sender = 0;
        $receiver = 0;
        $product_id = 0;
        if (
            (isset($msg['sender'])) &&
            (isset($msg['receiver'])) &&
            (isset($msg['product_id']))
        ) {
            $product_id = ((int)($msg['product_id']));
            $receiver = ((int)($msg['receiver']));
            $sender = ((int)($msg['sender']));
        }

        if (
            ($product_id < 1) ||
            ($receiver < 1) ||
            ($sender < 1)
        ) {
            return 'Sender or receiver was not set.';
        }

        if (
            ($receiver < 1) ||
            ($product_id < 1) ||
            ($sender < 1)
        ) {
            return 'Sender or receiver or product id were not set.';
        }

        if (
            $receiver ==  $sender
        ) {
            return 'Sender and receiver cannot be the same.';
        }
        $sender_user = User::find($sender);
        if ($sender_user == null) {
            return "Sender not found";
        }

        $receiver_user = User::find($receiver);
        if ($receiver_user == null) {
            return "Receiver not found";
        }

        $product = Product::find($product_id);
        if ($product == null) {
            return "Product not found";
        }

        $chat = new Chat();
        $chat->sender = $sender;
        $chat->receiver = $receiver;
        $chat->product_id = $product_id;
        $chat->body = isset($msg['body']) ? $msg['body'] : "";
        $chat->thread = "";
        $chat->received = false;
        $chat->seen = false;
        $chat->receiver_pic = $receiver_user->avatar;
        $chat->receiver_name = $receiver_user->name;
        $chat->sender_pic = $sender_user->avatar;
        $chat->sender_name = $sender_user->name;
        $chat->type = "text";
        $chat->contact = "";
        $chat->gps = "";
        $chat->file = "";
        $chat->image = "";
        $chat->audio = "";

        $chat->thread = Chat::get_chat_thread_id($chat->sender, $chat->receiver, $chat->product_id);

        if (!$chat->save()) {
            return "Failed to save message.";
        }

        return null;
    }

    public static function get_file_url($link)
    {
        $link = str_replace("public/", "", $link);
        $link = str_replace("public", "", $link);
        $link = "storage/" . $link;
        return $link;
    }

    public static function make_slug($str)
    {
        $slug =  strtolower(Str::slug($str, "-"));

        if (
            (!empty(Product::where("slug", $slug)->First())) ||
            (!empty(Profile::where("username", $slug)->First()))
        ) {
            $slug .= rand(100, 1000);
        }

        return $slug;
    }


    public static function upload_file($file)
    {
        if (!isset($file['tmp_name'])) {
            return "";
        }

        $path = Storage::putFile('/public/storage', $file['tmp_name']);
        return $path;
    }
    public static function upload_images($files)
    {


        if ($files == null || empty($files)) {
            return [];
        }
        if (!isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [];
        }

        $uploaded_images = array();
        if (isset($files['name'])) {
            ini_set('memory_limit', '512M');


            for ($i = 0; $i < count($files['name']); $i++) {
                if (
                    isset($files['name'][$i]) &&
                    isset($files['type'][$i]) &&
                    isset($files['tmp_name'][$i]) &&
                    isset($files['error'][$i]) &&
                    isset($files['size'][$i])
                ) {
                    $img['name'] = $files['name'][$i];
                    $img['type'] = $files['type'][$i];
                    $img['tmp_name'] = $files['tmp_name'][$i];
                    $img['error'] = $files['error'][$i];
                    $img['size'] = $files['size'][$i];
                    $ext = pathinfo($img['name'], PATHINFO_EXTENSION);

                    $file_name = time() . "-" . Utils::make_slug($img['name']) . "." . $ext;
                    $path = 'public/storage/' . $file_name;

                    $res = move_uploaded_file($img['tmp_name'], $path);
                    if (!$res) {
                        continue;
                    }

                    $thumn_name = 'thumb_' . $file_name;
                    $path_optimized = 'public/storage/' . $thumn_name;

                    $thumbnail = Utils::create_thumbail(
                        array(
                            "source" => "./" . $path,
                            "target" => $path_optimized,
                        )
                    );


                    $ready_image['src'] = $file_name;
                    $ready_image['thumbnail'] = $thumn_name;

                    $ready_image['user_id'] = Auth::id();
                    if (!$ready_image['user_id']) {
                        $ready_image['user_id'] = 1;
                    }

                    $_ready_image = new Image($ready_image);
                    $_ready_image->save();
                    $uploaded_images[] = $ready_image;
                }
            }
        }

        return $uploaded_images;
    }

    public static function create_thumbail($params = array())
    {
        ini_set('memory_limit', '-1');

        if (
            !isset($params['source']) ||
            !isset($params['target'])
        ) {
            return [];
        }

        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = false;
        $image->source_path = "" . $params['source'];
        $image->target_path = "" . $params['target'];


        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        }

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        $img_size = getimagesize($image->source_path); // returns an array that is filled with info

        $width = 300;
        $heigt = 300;

        if (isset($img_size[0]) && isset($img_size[1])) {
            $width = $img_size[0];
            $heigt = $img_size[1];
        }
        //dd("W: $width \n H: $heigt");

        if ($width < $heigt) {
            $heigt = $width;
        } else {
            $width = $heigt;
        }

        if (isset($params['width'])) {
            $width = $params['width'];
        }

        if (isset($params['heigt'])) {
            $width = $params['heigt'];
        }

        $image->jpeg_quality = 50;
        $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        if (!$image->resize($width, $heigt, ZEBRA_IMAGE_CROP_CENTER)) {
            return $image->source_path;
        } else {
            return $image->target_path;
        }
    }

    public static function get_jpeg_quality($_size)
    {
        $size = ($_size / 1000000);

        $qt = 50;
        if ($size > 5) {
            $qt = 10;
        } else if ($size > 4) {
            $qt = 13;
        } else if ($size > 2) {
            $qt = 15;
        } else if ($size > 1) {
            $qt = 17;
        } else if ($size > 0.8) {
            $qt = 50;
        } else if ($size > .5) {
            $qt = 80;
        } else {
            $qt = 90;
        }

        return $qt;
    }
}
