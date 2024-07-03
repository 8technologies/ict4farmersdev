<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GardenActivity extends Model
{
    use HasFactory;

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
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

    public function assigned_to()
    {
        $o = Administrator::find($this->person_responsible);
        if ($o == null) {
            $this->person_responsible = 1;
            $this->save();
        }
        return $this->belongsTo(Administrator::class, 'person_responsible');
    }


    //getter for administrator_text
    public function getAdministratorTextAttribute()
    {
        $o = Administrator::find($this->person_responsible);
        if ($o == null) {
            $this->person_responsible = 1;
            $this->save();
            $o = Administrator::find($this->person_responsible);
        }
        if ($o == null) {
            return 'N/A';
        }
        return $o->name;
    }



    public static function boot()
    {
        parent::boot();


        self::created(function ($m) {
            $acts = GardenActivity::where('administrator_id', $m->administrator_id)->orderBy('due_date', 'Asc')->get();
            $position = 0;
            if ($acts != null) {
                foreach ($acts as $key => $value) {
                    $position++;
                    $value->position = $position;
                    $value->save();
                }
            }
        });

        self::updating(function ($model) {
            // ... code here

        });

        self::updated(function ($model) {
            $garden = Garden::find($model->garden_id);
            if ($garden != null) {
                $garden->do_update();
            }
        });
 

        self::deleted(function ($model) {
            $garden = Garden::find($model->garden_id);
            if ($garden != null) {
                $garden->do_update();
            }
        });
    }

    //appends administrator_text
    protected $appends = ['administrator_text', 'garden_text'];

    //getter for garden_text
    public function getGardenTextAttribute()
    {
        $o = Garden::find($this->garden_id);
        if ($o == null) {
            $this->garden_id = 1;
            $this->save();
            $o = Garden::find($this->garden_id);
        }
        if ($o == null) {
            return 'N/A';
        }
        return $o->name;
    }
}
