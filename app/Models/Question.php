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
}
