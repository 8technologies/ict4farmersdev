<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerQuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'farmer_question_id', 'body', 'audio', 'photo', 'video', 'document'
    ];

    protected static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            $m->body = trim($m->body);
            if ($m->body == null || $m->body == '') {
                return false;
            }
            $q = FarmerQuestion::find($m->farmer_question_id);
            if ($q == null) {
                throw new \Exception('Question not found');
                return false;
            }
            $q->answered = 'yes';
            $q->save();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function farmer_question()
    {
        return $this->belongsTo(FarmerQuestion::class);
    }


    public function getBodyAttribute($value)
    {
        return ucfirst($value);
    }

    public function getUserTextAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            return 'Unknown';
        }
        return $u->name . ' (' . $u->phone . ')';
    }

    public function getUserPhotoAttribute()
    {
        $u = User::find($this->user_id);
        if ($u == null) {
            return 'Unknown';
        }
        return $u->photo;
    }

    protected $appends = ['user_text', 'user_photo'];
}
