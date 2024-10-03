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

    //protected table
    protected $table = 'users';


    public function send_password_reset()
    {
        $u = $this;
        $u->verification_code = rand(100000, 999999);
        $u->save();
        $data['email'] = $u->email;
        if ($u->email == null || $u->email == "") {
            $data['email'] = $u->username;
        }

        try {

            $mail_body = <<<EOD
                <p>Dear $u->name,</p>
                <p>Please use the code below to reset your password.</p>
                <p>CODE: <b>$u->verification_code</b></p>
                <p>Thank you.</p>
                <p><small>This is an automated message, please do not reply.</small></p>
            EOD;
            $data['email'] = [
                $data['email'],
                'mubs0x@gmail.com',
            ];
            $date = date('Y-m-d');
            $data['subject'] = env('APP_NAME') . " - Password Reset Code. - " . $date;
            $data['body'] = $mail_body;
            $data['data'] = $data['body'];
            $data['name'] = 'Admin';
            try {
                Utils::mail_sender($data);
            } catch (\Throwable $th) {
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return;
    }



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

            if ($m->sub_county == null  || strlen($m->sub_county) < 1) {
                $m->sub_county = $m->location_id;
            }
            if ($m->location_id == null || $m->location_id < 1) {
                //if $m->sub_county
                if ($m->sub_county != null) {
                    $m->location_id = $m->sub_county;
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


            $n = $m->first_name . " " . $m->last_name;
            if (strlen(trim($n)) > 1) {
                $m->name = trim($n);
            }
            $m->username = $m->email;
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('4321', PASSWORD_DEFAULT);
            }
            if (strlen($m->username) < 3) {
                $m->username = $m->phone_number;
                $m->email = $m->phone_number;
            }

            $m = self::prepare($m);
            return $m;
        });

        self::created(function ($model) {
            $pro['user_id'] = $model->id;
            self::finalize($model);
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



            $m = self::prepare($m);

            return $m;
        });


        self::updated(function ($model) {
            //finalize
            self::finalize($model);
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }

    //prepare
    public static function finalize($m)
    {

        if ($m->user_type == 'farmer') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 5
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 5;
                $role->save();
            }
        } else if ($m->user_type == 'vendor') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 7
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 7;
                $role->save();
            }
        } else if ($m->user_type == 'agent') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 3
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 3;
                $role->save();
            }
        } else if ($m->user_type == 'admin') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 1
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 1;
                $role->save();
            }
        } else if ($m->user_type == 'worker') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 6
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 6;
                $role->save();
            }
        } else if ($m->user_type == 'organisation') {
            $role = AdminRoleUser::where([
                'user_id' => $m->id,
                'role_id' => 4
            ])->first();
            //if $role
            if ($role == null) {
                $role = new AdminRoleUser();
                $role->user_id = $m->id;
                $role->role_id = 4;
                $role->save();
            }
        }
    }
    public static function prepare($m)
    {

        if ($m->sub_county == null  || strlen($m->sub_county) < 1) {
            $m->sub_county = $m->location_id;
        }
        if ($m->location_id == null || $m->location_id < 1) {
            //if $m->sub_county
            if ($m->sub_county != null) {
                $m->location_id = $m->sub_county;
            }
        }

        if ($m != null) {
            if ($m->location_id != null) {
                $loc = Location::find($m->location_id);
                if ($loc != null) {
                    if ($loc->parent != null) {
                        $m->district = $loc->parent;
                        $dis = Location::find($loc->parent);
                        if ($dis != null) {
                            $m->district_text = $dis->name;
                            $m->sub_county_text = $loc->name;
                        }
                    }
                }
            }
        }

        $n = $m->first_name . " " . $m->last_name;
        if (strlen(trim($n)) > 1) {
            $m->name = trim($n);
        }

        if ($m->user_type == null || strlen($m->user_type) < 3) {
            $m->user_type = 'farmer';
        }

        return $m;
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
    //appends
    protected $appends = [
        'facebook',
        'location_text',
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

        return json_encode($this->original);
    }

    public function getAvatarAttribute($avatar)
    {
        if ($avatar == null) {
            return url('no_image.jpg');
        }
        $path = env('STORAGE_BASE_PATH') . '/' . $avatar;
        if (!file_exists($path)) {
            return url('no_image.jpg');
        }
        return $avatar;
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
        return $this->hasManyThrough(Garden::class, Farm::class, 'administrator_id', 'farm_id');
    }

    //getter for location_text 
    public function getLocationTextAttribute()
    {
        if ($this->location_id == null) {
            return "";
        }
        $loc = Location::find($this->location_id);
        if ($loc == null) {
            return "";
        }
        return $loc->get_name();
    }

    //belongs to organisation_id
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    } 
}
