<?php

namespace App\Imports;

use App\User;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToModel;
use Spatie\Emoji\Emoji;

class UsersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        Mail::send('welcome',[],function($message)use($row){
            $message->to($row[0]);
            $message->subject(Emoji::globeShowingAmericas().' !!دنیای بدون مرز!! '.Emoji::globeShowingAmericas());
        });
        sleep(0.5);

    }
}
