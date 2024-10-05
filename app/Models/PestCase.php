<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestCase extends Model
{
    use HasFactory;

    //created boot
    protected static function boot()
    {
        parent::boot();
        static::created(function ($pest_case) {
            self::do_process($pest_case);
        });

        //updated
        static::updated(function ($pest_case) {
            self::do_process($pest_case);
        });
    }

    public static function do_process($pest_case)
    {
        $pest = Pest::find($pest_case->pest_id);
        $pest->pest_cases_count = PestCase::where('pest_id', $pest->id)->count();
        $pest->pest_recent_cases_count = PestCase::where('pest_id', $pest->id)->where('created_at', '>', now()->subDays(30))->count();


        $location_id = PestCase::select('location_id')
            ->where('pest_id', $pest->id)
            ->groupBy('location_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('location_id');

        $pest->common_district_id = $location_id;
        $pest->common_subcounty_id = $location_id;

        $pest->save();
    }
    /* 
    
                $table->integer('pest_cases_count')->default(0)->nullable();
            $table->integer('pest_recent_cases_count')->default(0)->nullable();
    */

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
        if (is_array($value))
            $this->attributes['images'] = json_encode($value);
    }
    //get images attribute
    public function getImagesAttribute($value)
    {
        return json_decode($value, true);
    }

  
}
