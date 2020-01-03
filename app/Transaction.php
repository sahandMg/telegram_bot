<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['status','account_id'];
    public function plan(){

        return $this->belongsTo(Plan::class);
    }

    public function account(){

        return $this->belongsTo(Accounts::class);
    }
}
