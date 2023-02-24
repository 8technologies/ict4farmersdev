<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestCase extends Model
{
    use HasFactory;

    //belongs to a pest
    public function pest()
    {
        return $this->belongsTo(Pest::class, 'pest_id');
    }

    //reported by a user
    public function reporter()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }
    
    //belongs to a garden
    public function garden()
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }
    //set images attribute
    public function setImagesAttribute($value)
    {
        if(is_array($value))
            $this->attributes['images'] = json_encode($value);
    }
    //get images attribute
    public function getImagesAttribute($value)
    {
        return json_decode($value,true);
    }
}
