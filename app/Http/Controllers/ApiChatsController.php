<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Chat;
use App\Models\City;
use App\Models\Country;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiChatsController
{
    public function notification_send(Request $request)
    {
        try {

            Utils::sendNotification(
                'Simple Test Message',
                '16308',
                $headings = 'Test title',
                data: [
                    'id' => '1',
                    'sender_id' => '1',
                    'receiver_id' => '2',
                    'chat_head_id' => '3',
                ]
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function send_sms(Request $request)
    {
        $res = Utils::send_sms([
            'to' => '+256706638494',
            'message' => 'Test message'
        ]);
        echo "<pre>";
        print_r($res);
        die("Romina");
    }
    public function send_message(Request $request)
    {
        $sender = 0;
        $receiver = 0;
        $product_id = 0;
        if (
            (isset($_POST['sender'])) &&
            (isset($_POST['receiver'])) &&
            (isset($_POST['product_id']))
        ) {
            $product_id = ((int)($_POST['product_id']));
            $receiver = ((int)($_POST['receiver']));
            $sender = ((int)($_POST['sender']));
        }

        if (
            ($product_id < 1) ||
            ($receiver < 1) ||
            ($sender < 1)
        ) {
            return Utils::response(['message' => 'Sender or receiver was not set.', 'status' => 0]);
        }

        $msg_resp = Utils::send_message($_POST);
        if ($msg_resp != null) {
            return Utils::response(['message' => $msg_resp, 'status' => 0]);
        }

        return Utils::response(['message' => "Sent successfully", 'status' => 1]);
    }

    public function index(Request $request)
    {
        $per_page = 1000;
        $thread = (string) ($request->thread ? $request->thread : "");
        $user_id = (string) ($request->user_id ? $request->user_id : "");
        $unread = (string) ($request->user_id ? $request->unread : "");

        return Chat::get_chat_threads($thread, $user_id, $unread);
    }

    public function threads(Request $request)
    {
        $user_id = (int) ($request->user_id ? $request->user_id : 0);
        return Chat::get_chat_threads($user_id);
    }
}
