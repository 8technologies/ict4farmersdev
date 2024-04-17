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

    //boot created
    protected static function boot()
    {
        parent::boot();
        static::created(function ($image) {
            try {
                $image->processThumbnail();
            } catch (\Exception $e) {
                //dd($e->getMessage());
            }
        });
    }


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

    //process thumbnail
    public function processThumbnail()
    {
        if ($this->src == null || strlen($this->src) < 2) {
            dd('src is null');
            return $this->src;
        }
        $path = env('STORAGE_BASE_PATH') . '/' . $this->src;
        //filename
        if (!file_exists($path)) {
            echo ('<br>File not found. => ' . $this->src." <==== <br>");
            return; 
        }
        $filename = basename($this->src);
        $path_optimized = env('STORAGE_BASE_PATH') . '/thumb_' . $filename;
        $thumbnail = Utils::create_thumbail(
            array(
                "source" =>  $path,
                "target" => $path_optimized,
            )
        );
        //get image size to mb
        $size = filesize($path_optimized);
        $size = $size / 1024 / 1024;
        $size = round($size, 2);
        $this->size = $size;
        $this->thumbnail = 'thumb_' . $filename;
        $this->save();
        echo ("<br> SUCCESS WITH ===> ".$this->thumbnail." <==========");
    }
}
