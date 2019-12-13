<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function plan(){

        return $this->belongsTo(Plan::class);
    }
}
