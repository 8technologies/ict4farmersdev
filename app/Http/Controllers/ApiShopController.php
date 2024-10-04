<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\BatchSession;
use App\Models\Category;
use App\Models\ChatHead;
use App\Models\ChatMessage;
use App\Models\DrugStockBatch;
use App\Models\Event;
use App\Models\Farm;
use App\Models\Image;
use App\Models\Movement;
use App\Models\Product;
use App\Models\SlaughterHouse;
use App\Models\SlaughterRecord;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;

class ApiShopController extends Controller
{

    use ApiResponser;



    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }

        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {

            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);

            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $conditions = [];
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }

    public function chat_start(Request $r)
    {
        $sender = null;
        if ($sender == null) {
            $administrator_id = Utils::get_user_id($r);
            $sender = Administrator::find($administrator_id);
        }
        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        $pro = Product::find($r->product_id);
        if ($pro == null) {
            return $this->error('Product not found.');
        }
        $product_owner = null;
        $customer = null;

        if ($pro->user == $sender->id) {
            $product_owner = $sender;
            $customer = $receiver;
        } else {
            $product_owner = $receiver;
            $customer = $sender;
        }

        $chat_head = ChatHead::where([
            'product_id' => $pro->id,
            'product_owner_id' => $product_owner->id,
            'customer_id' => $customer->id
        ])->first();
        if ($chat_head == null) {
            $chat_head = ChatHead::where([
                'product_id' => $pro->id,
                'customer_id' => $product_owner->id,
                'product_owner_id' => $customer->id
            ])->first();
        }

        if ($chat_head == null) {
            $chat_head = new ChatHead();
            $chat_head->product_id = $pro->id;
            $chat_head->product_owner_id = $product_owner->id;
            $chat_head->customer_id = $customer->id;
            $chat_head->product_name = $pro->name;
            $chat_head->product_photo = $pro->feature_photo;
            $chat_head->product_owner_name = $product_owner->name;
            $chat_head->product_owner_photo = $product_owner->photo;
            $chat_head->customer_name = $customer->name;
            $chat_head->customer_photo = $customer->photo;
            $chat_head->last_message_body = '';
            $chat_head->last_message_time = Carbon::now();
            $chat_head->last_message_status = 'sent';
            try {
                $chat_head->save();
                $chat_head = ChatHead::find($chat_head->id);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return $this->success($chat_head, 'Success');
    }





    public function chat_messages(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return $this->error('User not found!');
        }

        if (isset($r->chat_head_id) && $r->chat_head_id != null) {
            $messages = ChatMessage::where([
                'chat_head_id' => $r->chat_head_id
            ])->get();
            return $this->success($messages, 'Success');
        }
        $messages = ChatMessage::where([
            'sender_id' => $u->id
        ])->orWhere([
            'receiver_id' => $u->id
        ])->get();
        return $this->success($messages, 'Success');
    }



    public function chat_heads(Request $r)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }
        if ($u == null) {
            return $this->error('User not found.');
        }
        $chat_heads = ChatHead::where([
            'product_owner_id' => $u->id
        ])->orWhere([
            'customer_id' => $u->id
        ])->get();
        $chat_heads->append('customer_unread_messages_count');
        $chat_heads->append('product_owner_unread_messages_count');
        return $this->success($chat_heads, 'Success');
    }


    public function chat_mark_as_read(Request $r)
    {
        $receiver = Administrator::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        $chat_head = ChatHead::find($r->chat_head_id);
        if ($chat_head == null) {
            return $this->error('Chat head not found.');
        }
        $messages = ChatMessage::where([
            'chat_head_id' => $chat_head->id,
            'receiver_id' => $receiver->id,
            'status' => 'sent'
        ])->get();
        foreach ($messages as $key => $message) {
            $message->status = 'read';
            $message->save();
        }
        return $this->success($messages, 'Success');
    }

    public function chat_send(Request $r)
    {

        $administrator_id = Utils::get_user_id($r);
        $sender = Administrator::find($administrator_id);

        if ($sender == null) {
            return $this->error('User not found.');
        }

        if ($sender == null) {
            $administrator_id = Utils::get_user_id($r);
            $sender = Administrator::find($administrator_id);
        }
        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        $pro = Product::find($r->product_id);
        if ($pro == null) {
            return $this->error('Product not found.');
        }
        $product_owner = null;
        $customer = null;

        if ($pro->user == $sender->id) {
            $product_owner = $sender;
            $customer = $receiver;
        } else {
            $product_owner = $receiver;
            $customer = $sender;
        }

        $chat_head = ChatHead::where([
            'product_id' => $pro->id,
            'product_owner_id' => $product_owner->id,
            'customer_id' => $customer->id
        ])->first();
        if ($chat_head == null) {
            $chat_head = ChatHead::where([
                'product_id' => $pro->id,
                'customer_id' => $product_owner->id,
                'product_owner_id' => $customer->id
            ])->first();
        }

        if ($chat_head == null) {
            $chat_head = new ChatHead();
            $chat_head->product_id = $pro->id;
            $chat_head->product_owner_id = $product_owner->id;
            $chat_head->customer_id = $customer->id;
            $chat_head->product_name = $pro->name;
            $chat_head->product_photo = $pro->feature_photo;
            $chat_head->product_owner_name = $product_owner->name;
            $chat_head->product_owner_photo = $product_owner->photo;
            $chat_head->customer_name = $customer->name;
            $chat_head->customer_photo = $customer->photo;
            $chat_head->last_message_body = $r->body;
            $chat_head->last_message_time = Carbon::now();
            $chat_head->last_message_status = 'sent';
            $chat_head->save();
        }
        $chat_message = new ChatMessage();
        $chat_message->chat_head_id = $chat_head->id;
        $chat_message->sender_id = $sender->id;
        $chat_message->receiver_id = $receiver->id;
        $chat_message->sender_name = $sender->name;
        $chat_message->sender_photo = $sender->photo;
        $chat_message->receiver_name = $receiver->name;
        $chat_message->receiver_photo = $receiver->photo;
        $chat_message->body = $r->body;
        $chat_message->type = 'text';
        $chat_message->status = 'sent';
        $chat_message->save();
        $chat_head->last_message_body = $r->body;
        $chat_head->last_message_time = Carbon::now();
        $chat_head->last_message_status = 'sent';
        $chat_head->save();
        return $this->success($chat_message, 'Success');
    }




    public function products(Request $r)
    {
        $user_id = Utils::get_user_id($r);
        $items = Product::where([
            'status' => 1
        ])->orwhere([
            'user' => $user_id
        ])->get();

        return $this->success($items, 'Success');
    }

    public function products_delete(Request $r)
    {
        $pro = Product::find($r->id);
        if ($pro == null) {
            return $this->error('Product not found.');
        }
        try {
            $pro->delete();
            return $this->success(null, $message = "Sussesfully deleted!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to delete product.');
        }
    }




    public function product_create(Request $r)
    {

        $user_id = $r->user;
        $u = Administrator::find($user_id);

        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            !isset($r->id) ||
            $r->name == null ||
            ((int)($r->id)) < 1
        ) {
            return $this->error('Local parent ID is missing.');
        }

        $isEdit = false;

        $pro = Product::find($r->id);
        if ($pro != null) {
            $isEdit = true;
        } else {
            $pro = new Product();
        }

        $local_id = $r->local_id;


        if ($r->local_id == null || strlen($r->local_id) < 5) {
            return $this->error('Local is required. Upadate the App.');
        }


        if ($pro == null) {
            $pro = Product::where([
                'local_id' => $local_id
            ])->first();
            if ($pro != null) {
                $isEdit = true;
            } else {
                $pro = new Product();
            }
        }

        if ($r->name == null || strlen($r->name) < 3) {
            return $this->error('Name is missing.');
        }
        //$r->description
        if ($r->description == null || strlen($r->description) < 3) {
            return $this->error('Description is missing.');
        }
        //$r->price_1;
        if ($r->price_1 == null || strlen($r->price_1) < 1) {
            return $this->error('Price 1 is missing.');
        }
        //$r->price_1;
        if ($r->type == null || strlen($r->type) < 2) {
            //update the app
            return $this->error('You have old version of the app. Please update the app from the play store.');
        }

        //$r->category_id
        if ($r->sub_category_id == null || strlen($r->sub_category_id) < 1) {
            return $this->error('You have old version of the app. Please update the app from the play store now.');
        }
        $cat = Category::find($r->sub_category_id);
        if ($cat == null) {
            return $this->error('Category not found.');
        }
        $pro->sub_category_id = $cat->id;
        $pro->category = $cat->type;
        $pro->name = $r->name;
        $pro->description = $r->description;
        $pro->price_1 = $r->price_1;
        $pro->price_2 = $r->price_2;
        $pro->local_id = $r->local_id;
        $pro->summary = $r->data;
        $pro->sub_category = $r->sub_category_id;
        $pro->type = $r->type;
        $pro->p_type = $r->p_type;
        $pro->keywords = $r->keywords;
        $pro->metric = $r->metric;
        $pro->status = 0;
        if ($u->vendor_status == 'Approved') {
            $pro->status = 1;
        } else {
            $pro->status = 2;
        }
        $pro->currency = 1;
        $pro->user = $u->id;
        $pro->user_id = $u->id;
        $pro->supplier = $u->id;
        $pro->rates = 1;
        $pro->in_stock = 1;
        $imgs = Image::where([
            'parent_id' => $local_id
        ])->get();
        if ($imgs->count() > 0) {
            $pro->feature_photo = $imgs[0]->src;
        } else {
            $pro->feature_photo = 'no_image.jpg';
        }

        try {
            $pro->save();
            foreach ($imgs as $key => $img) {
                $img->product_id = $pro->id;
                $img->save();
            }
            try {
                $pro->processThumbnail();
            } catch (\Throwable $th) {
                //throw $th;
            }
            $msg = "Submitted uploaded successfully!";
            if ($isEdit) {
                $msg = "Submitted updated successfully!";
            }
            $pro = Product::find($pro->id);
            return $this->success($pro, $message = $msg, 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to upload product because of ' . $th->getMessage());
        }
    }



    public function upload_media(Request $request)
    {

        $administrator_id = Utils::get_user_id($request);
        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        if (
            !isset($request->parent_id) ||
            $request->parent_id == null
        ) {

            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID is missing. 1",
            ]);
        }


        if (
            !isset($request->parent_endpoint) ||
            $request->parent_endpoint == null ||
            (strlen(($request->parent_endpoint))) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Local parent ID endpoint is missing.",
            ]);
        }

        if (
            empty($_FILES)
        ) {
            return Utils::response([
                'status' => 0,
                'message' => "Files not found.",
            ]);
        }

        $images = Utils::upload_images_1($_FILES, false);
        $_images = [];

        if (empty($images)) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to upload files.',
                'data' => null
            ]);
        }

        $msg = "";
        foreach ($images as $src) {

            $local_parent_id = null;
            if ($request->local_parent_id != null && strlen($request->local_parent_id) > 3) {
                $local_parent_id = $request->local_parent_id;
            }
            if ($local_parent_id == null) {
                if ($request->parent_id != null && strlen($request->parent_id) > 3) {
                    $local_parent_id = $request->parent_id;
                }
            }
            if ($local_parent_id == null) {
                if ($request->product_id != null && strlen($request->product_id) > 3) {
                    $local_parent_id = $request->product_id;
                }
            }

            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->user_id = $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  $src;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->p_type =  $request->parent_endpoint;
            $img->parent_id =  $local_parent_id;
            $img->product_id =  null;
            $img->size = 0;
            $img->note = $request->note;
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
                $msg .= "Note not set. ";
            }

            //if parent_endpoint is Product
            if ($request->parent_endpoint == 'Product') {
                $pro = Product::where([
                    'local_id' => $local_parent_id
                ])->first();
                if ($pro != null) {
                    $img->product_id =  $pro->id;
                    if ($pro->feature_photo == null || strlen($pro->feature_photo) < 4) {
                        $pro->feature_photo = $src;
                        $pro->save();
                    }
                }
            }
            $img->save();
            $_images[] = $img;
        }
        //Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'data' => $_images,
            'message' => "File uploaded successfully.",
        ]);
    }
}
