<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function active()
    {
        if (!isset($this->profile)) {
            return false;
        }
        if ($this->profile == null) {
            return false;
        }
        if (!isset($this->profile->status)) {
            return false;
        }
        if ($this->profile->status == 1) {
            return true;
        }
        return false;
    }

    public function account_status()
    {
        if (!$this->profile) {
            return "not_active";
        }
        if (!$this->profile->status) {
            return "not_active";
        }
        if ($this->profile->status == "active") {
            return "active";
        }
        if (strlen($this->profile->cover_photo) > 4) {
            return "pending";
        }
        return "not_active";
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {


            $phone_number = Utils::prepare_phone_number($m->phone_number);
            $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
            if ($phone_number_is_valid) {
                $m->phone_number = $phone_number;
                $m->username = $phone_number;
            } else {
                if ($m->email != null) {
                    $m->username = $m->email;
                }
            }

            if ($m != null) {
                if ($m->location_id != null) {
                    $loc = Location::find($m->location_id);
                    if ($loc != null) {
                        if ($loc->parent != null) {
                            $m->district = $loc->parent;
                        }
                    }
                }
            }
            return $m;
        });

        self::created(function ($model) {
            $pro['user_id'] = $model->id;
            //Profile::create($pro);
        });

        self::updating(function ($m) {

            $phone_number = Utils::prepare_phone_number($m->phone_number);
            $phone_number_is_valid = Utils::phone_number_is_valid($phone_number);
            if ($phone_number_is_valid) {
                $m->phone_number = $phone_number;
                $m->username = $phone_number;
                $users = User::where([
                    'username' => $phone_number
                ])->orWhere([
                    'phone_number' => $phone_number
                ])->get();

                foreach ($users as $u) {
                    if ($u->id != $m->id) {
                        $u->delete();
                        continue;
                        $_resp = Utils::response([
                            'status' => 0,
                            'message' => "This phone number $m->phone_number is already used by another account",
                            'data' => null
                        ]);
                        die(json_encode($_resp));
                    }
                }
            }

            if ($m->first_name != null) {
                if (strlen($m->first_name) > 3) {
                    $m->profile_is_complete = 1;
                }
            }


            if ($m != null) {
                if ($m->location_id != null) {
                    $loc = Location::find($m->location_id);
                    if ($loc != null) {
                        if ($loc->parent != null) {
                            $m->district = $loc->parent;
                        }
                    }
                }
            }
            return $m;
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFacebookAttribute()
    {
        
        if($this->original['gender'] != 'Male' || $this->original['gender'] != 'Female'){
            $this->original['gender'] = 'Male';
        }
        return json_encode($this->original);
    }

    public function getAvatarAttribute($avatar)
    {
        if ($avatar == null) {
            return url('no_image.jpg');
        }
        return url($avatar);
    }

    public function getGenderAttribute($g)
    {
        if($g != 'Male' || $g != 'Female'){
            $g = 'Male';
        }
        return $g;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //grouped morph with group_id and group_text
    public function groupable()
    {
        return $this->morphTo(__FUNCTION__, 'group_text', 'group_id');
    }

    //has many enterprises/ gardens
    public function enterprises()
    {
        return $this->hasManyThrough(Garden::class, Farm::class,'administrator_id','farm_id');
    }
    //reports many pest cases
    public function pest_reports()
    {
        return $this->hasMany(PestCase::class,'administrator_id');
    }

}
