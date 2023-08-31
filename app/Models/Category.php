<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{



    public function getSlugAttribute($v)
    {
        if ($v == null) {
            $c = Category::find($this->id);
            if ($c != null) {
                $c->slug = Utils::make_slug($c->name);
                $c->save();
                return $c->slug;
            }
        }

        return $v;
    }




    protected $fillable = ['parent', 'order', 'name',];

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public static function get_subcategories()
    {
        $items = [];
        foreach (Category::all() as $key => $v) {
            $parent = ((int)($v->parent));
            $_name = "";
            if ($parent < 1) {
                continue;
            }
            $_name = $v->name;
            $items[$v->id] = $_name;
        }
        return $items;
    }

    public function kids()
    {
        return $this->hasMany(Category::class, "parent");
    }

    public function getImageAtrribute($img)
    {
        if (Str::contains($img, 'storage/')) {
            $img = str_replace('storage/', '', $img);
        }
        return $img;
    }
    public function sub_categories()
    {
        return $this->hasMany(Category::class, "parent");
    }

    public static function get_top_categories($max = 2)
    {
        $_cats = Category::all();
        $cats = [];
        $top_cats = [];

        foreach ($_cats as $key => $cat) {
            $cats[$cat->id . ""] = count($cat->get_products());
        }

        arsort($cats);
        $x = 0;
        foreach ($cats as $key => $value) {
            $id = (int)($key);
            $c = Category::find($id);
            if ($c == null) {
                continue;
            }
            $c->count = ((int)($value));

            $tot = 0;
            $pros = $cat->get_products();
            foreach ($pros as $p) {
                $price  = ((int)(str_replace(',', "", $p->price)));
                $tot += $price;
            }
            $c->tot = $tot;
            $top_cats[] = $c;
            $x++;
            if ($x > $max) {
                break;
            }
        }
        return $top_cats;
    }
    public function get_products()
    {
        return Product::where('category_id', $this->id)->get();
    }

    public function products()
    {
        return $this->hasMany(Product::class, "category_id");
    }

    //has many questions asked about it
    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->slug =  Str::slug($model->name, '-');
        });

        self::updating(function ($model) {
            $model->slug =  Str::slug($model->name, '-');
        });
    }
}
