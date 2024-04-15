<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'src',
        'thumbnail',
        'user_id',
        'product_id',
        'user_id',
        'size',
        'width',
        'height',
        'src',
        'name',
        'thumbnail',
        'parent_id',
        'parent_endpoint',
        'administrator_id',
        'note',
        'p_type'
    ];
}
