<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerQuestion extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $m->body = trim($m->body);
            if ($m->body == null || $m->body == '') {
                return false;
            }
            /*             $q = FarmerQuestion::where('body', $m->body)->first();
            if ($q != null) {
                throw new \Exception('Question already asked by ' . $q->phone);
            } */
            $u = User::find($m->user_id);
            if ($u != null) {
                if ($m->phone == null || $m->phone == '') {
                    $m->phone = $u->phone;
                }
                if ($m->district_model_id == null || $m->district_model_id == '') {
                    $m->district_model_id = $u->district_id;
                }
            }
            if ($m->district_model_id ==  null || $m->district_model_id == '') {
                $m->district_model_id = 0;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_model_id');
    }
    public function farmer_question_answers()
    {
        return $this->hasMany(FarmerQuestionAnswer::class);
    }

    public function getUserTextAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            return 'Unknown';
        }
        return $u->name;
    }
    public function getDistrictTextAttribute()
    {
        $d = Location::find($this->district_model_id);
        if ($d == null) {
            return 'Unknown';
        }
        return $d->name;
    }

    public function getUserPhotoAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            return '';
        }
        return $u->photo;
    }
    public function getAnswersCountAttribute()
    {
        return $this->farmer_question_answers()->count();
    }

    public $extends = [
        'user_text',
        'user_photo',
        'district_text',
        'answers_count',
    ];
}
