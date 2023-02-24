<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    //belongs a category
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //has many answers
    public function answers() {
        return $this->morphMany(Answer::class, 'question_id');
    }
    
    //asked by a user
    public function asked_by() {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    //mark as answered
    public function markAsAnswered() {
        $this->answered = true;
        $this->save();
    }
}
