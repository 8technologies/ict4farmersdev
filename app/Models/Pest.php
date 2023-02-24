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
}
