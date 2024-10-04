<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHead extends Model
{
    use HasFactory;

    public function getCustomerUnreadMessagesCountAttribute()
    {
        return ChatMessage::where('chat_head_id', $this->id)
            ->where('receiver_id', $this->customer_id)
            ->where('status', 'sent')
            ->count();
    }
    public function getProductOwnerUnreadMessagesCountAttribute()
    {
        return ChatMessage::where('chat_head_id', $this->id)
            ->where('receiver_id', $this->product_owner_id)
            ->where('status', 'sent')
            ->count();
    }

    //getter for last_message_body
    public function getLastMessageBodyAttribute()
    {
        $m = ChatMessage::where('chat_head_id', $this->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($m == null) {
            return "";
        }
        return $m->body;
    }
    
    //getter for last_message_time
    public function getLastMessageTimeAttribute()
    {
        $m = ChatMessage::where('chat_head_id', $this->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($m == null) {
            return "";
        }
        return $m->created_at;
    }



    //last_seen
    protected $appends = [
        'customer_unread_messages_count',
        'product_owner_unread_messages_count',
        'last_message_body',
        'last_message_time'
    ];
}
