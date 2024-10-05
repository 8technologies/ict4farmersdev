<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pest extends Model
{
    use HasFactory;

    //has many pest cases
    public function pest_cases()
    {
        return $this->hasMany(PestCase::class, 'pest_id', 'id');
    }

    //getter for common_subcounty_text
    public function getCommonSubcountyTextAttribute()
    { 
        $loc = Location::find($this->common_subcounty_id);
        if ($loc != null) {
            return $loc->get_name();
        } else {
            return "N/A";
        }
    }

    //append for common_subcounty_text
    protected $appends = ['common_subcounty_text'];
}
