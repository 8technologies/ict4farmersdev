<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $g = Garden::find($m->garden_id);
            if ($g != null) {
                if ($g->administrator_id != null) {
                    $m->administrator_id = $g->administrator_id;
                }
            }

            //check if type is not set
            if ($m->type == null) {
                throw new \Exception("Type is required");
            }
            if ($m->type != 'Income' && $m->type != 'Expenditure') {
                throw new \Exception("Invalid type");
            }
            $amount = abs(((int)($m->amount)));
            if($amount == 0 ){
                throw new \Exception("Amount is required");
            } 
            if ($m->type == 'Expenditure') {
                $amount = -1 * $amount;
            }
            $m->amount = $amount;
            return $m;
        });

        self::updated(function ($model) {
            $garden = Garden::find($model->garden_id);
            if ($garden != null) {
                $garden->do_update();
            }
        });

        self::created(function ($model) {
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



    public function owner()
    {
        $o = Administrator::find($this->administrator_id);
        if ($o == null) {
            $this->administrator_id = 1;
            $this->save();
        }
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }


    public function creator()
    {
        $o = Administrator::find($this->created_by);
        if ($o == null) {
            $this->created_by = 1;
            $this->save();
        }
        return $this->belongsTo(Administrator::class, 'created_by');
    }

    public function enterprise()
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }

    //garden_text
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

    //appends garden_text
    protected $appends = ['garden_text']; 


   /*  public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y g:i A');
    } */

    /* public function getAmountAttribute($v)
    {
        return "" . number_format($v);
    } */
}
