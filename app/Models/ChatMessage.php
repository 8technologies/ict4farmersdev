<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            try {
                $head = ChatHead::find($model->chat_head_id);
                $title = $model->sender_name;
                if ($head != null) {
                    $title = $head->product_name . ' - ' . $model->sender_name;
                }

                $receiver = User::find($model->receiver_id);
                if ($receiver != null) {
                    $last_seen = null;
                    if ($receiver->last_seen != null) {
                        try {
                            $last_seen = Carbon::parse($receiver->last_seen);
                        } catch (\Throwable $th) {
                            $last_seen = null;
                        }
                    }
                    if ($last_seen == null || $last_seen->diffInHours(Carbon::now()) > 12) {
                        $sms = "ICT4Farmers. You have a new message from " . ' about your product: ' . $model->product_name . ". Open the App to reply.";
                        $phone_number = Utils::prepare_phone_number($receiver->phone_number);

                        try {
                            if (Utils::phone_number_is_valid($phone_number)) {
                                Utils::send_sms([
                                    'to' => $phone_number,
                                    'message' => $sms
                                ]);
                            }
                        } catch (\Throwable $th) {
                            //throw $th;
                        }


                        $receiver->last_seen = Carbon::now();
                        $receiver->save();
                    }
                }

                Utils::sendNotification(
                    $model->body,
                    $model->receiver_id,
                    $headings = $title,
                    data: [
                        'id' => $model->id,
                        'sender_id' => $model->sender_id,
                        'receiver_id' => $model->receiver_id,
                        'chat_head_id' => $model->chat_head_id,
                    ]
                );
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }
}
