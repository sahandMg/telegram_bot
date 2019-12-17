<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ovpn extends Model
{
    protected $fillable = ['used','user_id','updated_at','expires_at'];
}
