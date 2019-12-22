<?php

namespace App\Console\Commands;

use App\Accounts;
use App\Server;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Emoji\Emoji;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

class CheckAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        $accounts = Accounts::where('used',1)->get();
        $freeAccounts = $accounts->where('plan_id',3);
        $monthly = $accounts->where('plan_id',1);
        $three_monthly = $accounts->where('plan_id',2);
        $servers = Server::where('status','up')->get();
        foreach ($freeAccounts as $account){

            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0 ){

                foreach ($servers as $server){
                    $ch = curl_init($server->ip.':9090?id='.$account->username);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $this->sendNotif($account);
                    $account->delete();
                }
            }

        }
        foreach ($monthly as $account){

            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0 ){

                foreach ($servers as $server){
                    $ch = curl_init($server->ip.':9090?id='.$account->username);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $this->sendNotif($account);
                    $account->delete();
                }
            }

        }

        foreach ($three_monthly as $account){

            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0  ){

                foreach ($servers as $server){
                    $ch = curl_init($server->ip.':9090?id='.$account->username);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $this->sendNotif($account);
                    $account->delete();
                }
            }

        }

        DB::commit();

    }
    private function sendNotif($account){

        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));

        $msg = [
            'chat_id' => $account->user_id,
            'text' => Emoji::redCircle()." حساب شما با نام کاربری $account->username منقضی شده است. ".Emoji::redCircle(),
            'parse_mode' => 'HTML',
        ];
        $data = array($msg);
        $jsonData = json_encode($data);
        $ch = curl_init('https://vitamin-g.ir/api/hook?type=warning');
        curl_setopt($ch, CURLOPT_USERAGENT, 'JOY VPN HandShake');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        curl_exec($ch);
        curl_close($ch);

        $telegram->sendMessage($msg);
    }
}
