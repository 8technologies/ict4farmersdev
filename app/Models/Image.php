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


    //getter for thumbnail
    public function getThumbnailAttribute($value)
    {
        if ($value == null || strlen($value) < 2) {
            return $this->src;
        }
        $path = env('STORAGE_BASE_PATH') . '/' . $value;
        if (!file_exists($path)) {
            return $this->src;
        }
        return $value;
    }
}
