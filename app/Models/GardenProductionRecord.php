<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenProductionRecord extends Model
{
    use HasFactory;

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y g:i A');
    }

    public function getGardenNameAttribute()
    {
        $g = Garden::find($this->garden_id);
        if ($g == null) {
            return "-";
        } 
        return $g->name;
    }

    public function setImagesAttribute($images)
{
    if (is_array($images)) {
        $this->attributes['images'] = json_encode($images);
    }
}

public function getImagesAttribute($images)
{
    return json_decode($images, true);
}


    public function owner()
    {
        $o = Administrator::find($this->administrator_id);
        if($o == null){
            $this->administrator_id = 1;
            $this->save();
        }
        return $this->belongsTo(Administrator::class,'administrator_id');
    }



    public function enterprise()
    {  
        $o = Garden::find($this->garden_id);
        if ($o == null) {
            $this->garden_id = 1;
            $this->save();
        }
        return $this->belongsTo(Garden::class, 'garden_id');
    }


    protected $appends = [
        'garden_name',
    ];
}
