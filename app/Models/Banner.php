<?php

namespace App\Models;

use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;


class Banner extends Model
{
    use HasFactory;
    use ModelTree, AdminBuilder;
    protected $table = 'banners';
    protected $appends = ['link'];



    public function getLinkAttribute()
    {

        $link = url("");
        if ($this->type == 1) {
            $car = Category::find($this->category_id);
            if ($car != null) {
                $link = url($car->slug);
            }
        } else if ($this->type == 2) {
            $car = Product::find($this->product_id);
            if ($car != null) {
                $link = url($car->slug);
            }
        } else if ($this->type == 3) {
            $link =  $this->position;
        }
        return $link;
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->title =  $model->name;
            $model->position =  0;
            return $model;
        });

        self::updating(function ($model) {
            $model->title =  $model->name;
            $model->position =  0;
            return $model;
        });
    }
}
