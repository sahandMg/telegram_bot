<?php

namespace App\Console\Commands;

use App\Accounts;
use App\Server;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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


        $this->expire();

        $this->reminder_week();
        $this->reminder_day();

    }
    private function expire(){

        DB::beginTransaction();
        $accounts = Accounts::where('used',1)->get();
//        $freeAccounts = $accounts->where('plan_id',3);
//        $monthly = $accounts->where('plan_id',1);
//        $three_monthly = $accounts->where('plan_id',2);
        $servers = Server::where('status','up')->get();
        foreach ($accounts as $account){

            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0 ){

                foreach ($servers as $server){
                    $ch = curl_init($server->ip.':9090?id='.$account->username);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    $this->sendNotif($account);
//                    $account->delete();
                }
            }

        }
//        foreach ($monthly as $account){
//
//            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0 ){
//
//                foreach ($servers as $server){
//                    $ch = curl_init($server->ip.':9090?id='.$account->username);
//                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
//                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                    $result = curl_exec($ch);
//                    $this->sendNotif($account);
////                    $account->delete();
//                }
//            }
//
//        }
//
//        foreach ($three_monthly as $account){
//
//            if(Carbon::now()->diffInDays(Carbon::parse($account->expires_at)) == 0  ){
//
//                foreach ($servers as $server){
//                    $ch = curl_init($server->ip.':9090?id='.$account->username);
//                    curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
//                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                    $result = curl_exec($ch);
//                    $this->sendNotif($account);
////                    $account->delete();
//                }
//            }
//
//        }

        DB::commit();

    }
// send a week before expiration
    private function reminder_week(){

        $accounts = Accounts::where('used',1)->where('expires_at','>',Carbon::now())->get();
//        $monthly = $accounts->where('plan_id',1);
//        $three_monthly = $accounts->where('plan_id',2);
        foreach ($accounts as $account){

            $trans = Transaction::where('account_id',$account->id)->where('status','paid')->first();
            $plan = \App\Plan::where('id',$trans->plan_id)->first();
            $target_date = Carbon::parse($account->expires_at);
            $diff = Carbon::now()->diffInDays($target_date);
            if(Carbon::now() < $target_date && $diff == 7){
                if(!is_null($trans->email)){
                    $account->expires_at = \Morilog\Jalali\Jalalian::fromCarbon($target_date)->format('%d %B %Y');
                    Mail::send('reminder', ['account' => $account, 'trans' => $trans,'plan'=> $plan], function ($message) use($trans) {
                        $message->to($trans->email);
                        $message->subject('یادآوری تمدید حساب');
                    });
                }else{

                    $api = new \Kavenegar\KavenegarApi( env('SMS') );
                    $sender = "10008000800600";
                    $receptor = array($trans->phone);
                    $message =
                        'یادآوری تمدید حساب JOY VPN.'
                        .' کاربر گرامی، تنها ۷ روز از اعتبار حساب شما باقی مانده. جهت تمدید حساب با نام '.$accounts->username
                        .' با قیمت '.$trans->amount.' تومان '
                        .' به لینک مراجعه کنید '
                        .route('tamdid')."?usr=$account->username&id=$account->user_id&trans_id=$trans->trans_id";
                    $api->Send($sender,$receptor,$message);
                }
            }
        }

    }

    private function reminder_day(){

        $accounts = Accounts::where('used',1)->where('expires_at','>',Carbon::now())->get();
//        $monthly = $accounts->where('plan_id',1);
//        $three_monthly = $accounts->where('plan_id',2);
        foreach ($accounts as $account){

            $trans = Transaction::where('account_id',$account->id)->where('status','paid')->first();
            $plan = \App\Plan::where('id',$trans->plan_id)->first();
            $target_date = Carbon::parse($account->expires_at);
            $diff = Carbon::now()->diffInDays($target_date);
            if(Carbon::now() < $target_date && $diff == 1){
                if(!is_null($trans->email)){
                    $account->expires_at = \Morilog\Jalali\Jalalian::fromCarbon($target_date)->format('%d %B %Y');
                    Mail::send('reminder', ['account' => $account, 'trans' => $trans,'plan'=> $plan], function ($message) use($trans) {
                        $message->to($trans->email);
                        $message->subject('یادآوری تمدید حساب');
                    });
                }else{

                    $api = new \Kavenegar\KavenegarApi( env('SMS') );
                    $sender = "10008000800600";
                    $receptor = array($trans->phone);
                    $message =
                        'یادآوری تمدید حساب JOY VPN.'
                        .' کاربر گرامی، تنها 1 روز از اعتبار حساب شما باقی مانده. جهت تمدید حساب با نام '.$accounts->username
                        .' با قیمت '.$trans->amount.' تومان '
                        .' به لینک مراجعه کنید '
                        .route('tamdid')."?usr=$account->username&id=$account->user_id&trans_id=$trans->trans_id";
                    $api->Send($sender,$receptor,$message);
                }
            }
        }

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

    }
}
