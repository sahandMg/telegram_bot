<?php

namespace App\Imports;

use App\Accounts;
use App\Server;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;

class AccountsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $servers = Server::where('status', 'up')->get();
        foreach ($servers as $server) {
            $ch = curl_init($server->ip . ':9095?username=' . $row[0].'&password='.$row[1]);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
        }
        return new Accounts([
            'username'=>$row[0],
            'password'=>$row[1],
            'plan_id'=>$row[2],
            'used'=> 0,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
        ]);
    }
}
