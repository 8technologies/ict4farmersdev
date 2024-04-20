<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use phpDocumentor\Reflection\Types\This;
use Psy\CodeCleaner\ValidConstructorPass;

use function PHPUnit\Framework\fileExists;

class Product extends Model
{
    use HasFactory;

    //fillables



    public function getPriceTextAttribute()
    {
        return config('app.currency') . " " . number_format((int)($this->price));
    }

    /*   public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans();
    } */


    public function getQuantityAttribute($value)
    {
        return (int)($value);
    }


    public function getPriceAttribute($value)
    {
        return $value;
    }


    //static prepare
    public static function prepare($m)
    {
        $sub = Category::find($m->sub_category_id);
        if ($sub != null) {
            $m->category_id = $sub->parent;
            $cat = Category::find($m->category_id);
            if ($cat != null) {
                $m->category = $cat->name;
            } else {
                $m->category = $sub->name;
                $m->category_id = 1;
            }
            $m->sub_category = $sub->name;
        } else {
            $m->category_id = 1;
            $m->sub_category_id = 1;
        }
        $dis = Location::find($m->city_id);
        if ($dis != null) {
            $m->country_id = $dis->parent;
        } else {
            $m->country_id = 1;
            $m->city_id = 1;
        }

        if ($m->price == null || strlen($m->price) < 1) {
            $m->price = ($m->price_1);
            $m->price_2 = ($m->price_1);
        }

        try {
            $m->price = abs((int)($m->price));
        } catch (\Exception $e) {
            //dd($e);
        }
        try {
            $m->quantity = abs((int)($m->quantity));
        } catch (\Exception $e) {
            //dd($e);
        }

        $m->price = ($m->price);
        $m->price_1 = $m->price;
        $m->price_2 = $m->price;

        if ($m->feature_photo != null && strlen($m->feature_photo) > 2) {
            $imagePath = env('STORAGE_BASE_PATH') . '/' . $m->feature_photo;
            //check if file exists
            if (!file_exists($imagePath)) {
                $m->feature_photo = 'no_image.jpg';
                $m->thumbnail = 'no_image.jpg';
            } else {
                $opt_path = env('STORAGE_BASE_PATH') . '/thumb_' . $m->feature_photo;
                $original_path = env('STORAGE_BASE_PATH') . '/' . $m->feature_photo;
                $thumbnail = Utils::create_thumbail(
                    array(
                        "source" =>  $original_path,
                        "target" => $opt_path,
                    )
                );
                $m->thumbnail = 'thumb_' . $m->feature_photo;
            }
        }

        return $m;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($p) {
            $p->slug = Utils::make_slug($p->name);
            $vendor = User::find($p->user_id);
            if ($vendor == null) {
                $vendor = User::find($p->user);
                if ($vendor == null) {
                    throw new \Exception("Vendor not found");
                }
            }
            $p->user = $vendor->id;
            $p->user_id = $vendor->id;
            if ($vendor->vendor_status == 'Approved') {
                $p->status = 1;
            } else {
                $p->status = 2;
            }



            $p = Product::prepare($p);
            return $p;
        });
        self::updating(function ($p) {
            $p = Product::prepare($p);
            return $p;
        });



        static::deleting(function ($model) {

            $thumbs = json_decode($model->images);
            if ($thumbs != null) {
                foreach ($thumbs as $key => $value) {
                    if (isset($value->thumbnail)) {
                        if (Storage::delete($value->thumbnail)) {
                            //echo "GOOD thumbnail <hr>";
                        }
                    }

                    if (isset($value->src)) {
                        if (Storage::delete($value->src)) {
                            // echo "GOOD  src <hr>";
                        }
                    }
                }
            }
        });
    }

    public function owner()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            $this->user_id = 1;
            $this->save();
        }
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location()
    {
        $loc = Location::find($this->city_id);
        if ($loc == null) {
            $this->city_id = 1;
            $this->save();
        }
        return $this->belongsTo(Location::class, 'city_id');
    }


    public function pro_category()
    {
        $c = Category::find($this->category_id);
        if ($c == null) {
            $this->sub_category_id = 1;
            try {
                $this->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        return $this->belongsTo(Category::class, "category_id");
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }


    public function sub_category()
    {
        return $this->belongsTo(Category::class, "sub_category_id");
    }

    public function get_name_short($min_length = 50)
    {
        if (strlen($this->name) > $min_length) {
            return substr($this->name, 0, $min_length) . "...";
        }
        return $this->name;
    }
    public function get_thumbnail()
    {

        $thumbnail = url('no_image.jpg');
        $hasPic = false;
        if ($this->feature_photo == null || strlen($this->feature_photo) < 2) {
            $hasPic = false;
        } else {
            $hasPic = true;
        }
        if ($this->feature_photo == 'no_image.jpg') {
            $hasPic = false;
        }

        //img path 
        $path = env('STORAGE_BASE_PATH') . '/' . $this->feature_photo;
        if (!file_exists($path)) {
            $hasPic = false;
        } else {
            $hasPic = true;
        }

        //thumbnail path
        $thumb_path  = env('STORAGE_BASE_PATH') . '/' . $this->thumbnail;
        if (!file_exists($thumb_path)) {
            if ($hasPic) {
                $thumb_path  = env('STORAGE_BASE_PATH') . '/thumb_' . $this->feature_photo;
                try {
                    $thumbnail = Utils::create_thumbail(
                        array(
                            "source" =>  $path,
                            "target" => $thumb_path,
                        )
                    );
                    //
                    if (!file_exists($thumb_path)) {
                        $hasPic = false;
                    } else {
                        $this->thumbnail = 'thumb_' . $this->feature_photo;
                        try {
                            $this->save();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }

        $thumb_link = "";
        //check if $this->thumbnail exists
        if ($this->thumbnail != null && strlen($this->thumbnail) > 2) {
            $thumbnail = env('STORAGE_BASE_PATH') . '/' . $this->thumbnail;
            if (file_exists($thumbnail)) {
                $thumb_link = url('public/storage/' . $this->thumbnail);
                return $thumb_link;
            }
        }

        if ($hasPic) {
            $thumb_link = url('public/storage/' . $this->feature_photo);
        } else {
            $thumb_link = url('public/storage/no_image.jpg');
        }
        return $thumb_link;
    }

    public function get_images()
    {
        $images = [];
        if ($this->images != null) {
            if (strlen($this->images) > 3) {
                $images_json = json_decode($this->images);
                foreach ($images_json as $key => $img) {
                    $img->src = url('/storage/' . $img->src);
                    $img->thumbnail = url('/storage/' . $img->thumbnail);
                    $images[] = $img;
                }
            }
        }

        foreach ($this->pics as $key => $img) {
            $images[] = $img;
        }
        return $images;
    }



    protected $appends = [
        'price_text',
        'seller_name',
        'seller_phone',
        'category_name',
        'city_name',
        'img',
    ];

    public function getImgAttribute($v)
    {
        if ($this->images == null) {
            return url('/no_image.jpg');
        }
        if (strlen($this->images) < 2) {
            return url('/no_image.jpg');
        }
        $img = json_decode($this->thumbnail);
        if (isset($img->thumbnail)) {
            if ($img->thumbnail != null) {
                $url = url('storage/' . $img->thumbnail);
                return $url;
            }
        }
        return url('/no_image.jpg');
    }

    public function getCityNameAttribute($value)
    {
        $city_id = (int)($this->city_id);
        $city = City::find($city_id);
        if ($city == null) {
            return "-";
        }
        $c = $city->country;
        if ($c != null) {
            return $c->name . ", " . $city->name;
        }
        return $city->name;
    }


    public function getCategoryNameAttribute()
    {

        $name = "-";
        $cat = Category::find($this->sub_category_id);
        if ($cat == null) {
            return "";
        } else { 
            $name = $cat->name;
        }
        return $name;
    }

    public function getSellerNameAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            $u = new User();
        }
        if ($u->company_name == null || (strlen($u->company_name) < 2)) {
            return $u->name;
        } else {
            return $u->company_name;
        }
    }

    public function getSellerPhoneAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            $u = new User();
        }

        if ($u->phone_number != null || (strlen($u->phone_number) > 2)) {
            return $u->phone_number;
        } else {
            return "-";
        }
    }


    public function init_attributes()
    {

        $attributes = json_decode($this->attributes['attributes']);
        if ($attributes == null) {
            $attributes = [];
        }
        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Nature of offer';
        $att->units = '';
        $att->value = $this->nature_of_offer;
        $attributes[] = $att;


        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Quantity available';
        $att->units = '';
        $att->value = $this->quantity;
        if ($att->value == 0) {
            $att->value = 1;
        }
        $attributes[] = $att;


        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Category';
        $att->units = '';
        $att->value = $this->category_name;;
        $attributes[] = $att;


        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Location';
        $att->units = '';
        $att->value = $this->city_name;
        $attributes[] = $att;


        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Offered by';
        $att->units = '';
        $att->value = $this->seller_name;
        $attributes[] = $att;

        $att = new Attribute();
        $att->type = 'text';
        $att->name = 'Posted';
        $att->units = '';
        $att->value = $this->created_at;
        $attributes[] = $att;

        $this->attributes['attributes'] =  json_encode($attributes);
    }

    public function get_price()
    {
        return ((int)(str_replace(',', '', $this->price)));
    }

    public function get_quantity()
    {
        return ((int)(str_replace(',', '', $this->quantity)));
    }




    protected $fillable = [
        'name',
        'user_id',
        'category_id',
        'sub_category_id',
        'price',
        'description',
        'city_id',
        'country_id',
        'slug',
        'thumbnail',
        'status',
        'attributes',
        'images',
        'city',
    ];

    public function getRatesAttribute()
    {
        $imgs = Image::where('parent_id', $this->id)->orwhere('product_id', $this->id)->get();
        return json_encode($imgs);
    }

    protected $casts = [
        'data' => 'json',
    ];

    //hasmnany Image
    public function images()
    {
        return $this->hasMany(Image::class, 'product_id');
    }
    //hasmnany Image
    public function pics()
    {
        return $this->hasMany(Image::class, 'product_id');
    }


    public function processThumbnail()
    {
        if ($this->feature_photo == 'no_image.jpg') {
            $this->thumbnail = 'no_image.jpg';
            $this->save();
            return;
        }

        $hasImage = false;
        if ($this->feature_photo == null || strlen($this->feature_photo) < 2) {
            $hasImage = false;
        } else {
            $hasImage = true;
        }

        if ($hasImage) {
            $path = env('STORAGE_BASE_PATH') . '/' . $this->feature_photo;
            if (!file_exists($path)) {
                $hasImage = false;
            } else {
                $hasImage = true;
            }
        }

        if ($hasImage) {
            $filename = basename($this->feature_photo);
            $thumb_name = 'thumb_' . $filename;
            $path_optimized = env('STORAGE_BASE_PATH') . '/' . $thumb_name;
            $thumbnail = Utils::create_thumbail(
                array(
                    "source" =>  $path,
                    "target" => $path_optimized,
                )
            );
            if (!file_exists($path_optimized)) {
                $this->feature_photo == 'no_image.jpg';
                $this->thumbnail = 'no_image.jpg';
                $this->save();
                return;
            } else {
                $this->thumbnail = $thumb_name;
                $this->save();
            }
        } else {
            $this->feature_photo == 'no_image.jpg';
            $this->thumbnail = 'no_image.jpg';
            $this->save();
        }
    }
}
