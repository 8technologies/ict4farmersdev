<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    //is made by a user
    public function user() {
        return $this->belongsTo(User::class, 'posted_by');
    }

    //belongs to a question / answer
    public function commentable() {
        return $this->morphTo();
    }

    //mark as accepted
    public function markAsAccepted() {
        $this->accepted = true;
        $this->save();
    }

}
