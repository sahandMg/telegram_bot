<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    protected $fillable = ['used','user_id','updated_at','expires_at'];
}
