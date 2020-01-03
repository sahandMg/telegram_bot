<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    protected $guarded = [];

    public function plan(){

        return $this->belongsTo(Plan::class);
    }
}
