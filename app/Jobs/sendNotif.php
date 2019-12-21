<?php

namespace App\Jobs;

use App\Accounts;
use App\Ovpn;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;

class sendNotif implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $trans;
    public function __construct($trans)
    {
        $this->trans = $trans;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $trans = $this->trans;
        $transactionId = $trans->trans_id;
        $orderID = $transactionId;

        // update created transaction record
        DB::beginTransaction();
        DB::connection('mysql')->table('transactions')->where('trans_id', $orderID)->update([
            'status' => 'paid'
        ]);
        if($trans->service == 'cisco'){
            $account = Accounts::where('user_id',$trans->user_id)->where('used',1)->first();
            // it means that user updated his account. it's NOT a new account
            if($account !== null){
                $account->update(['expires_at'=> Carbon::now()->addMonths($trans->plan->month)]);
            }else{

                $account = Accounts::where('plan_id',$trans->plan_id)->where('used',0)->first();
                $account->update(['used'=>1,'user_id'=>$trans->user_id,'expires_at'=>Carbon::now()->addMonths($trans->plan->month)]);
            }

        }elseif ($trans->service == 'openvpn'){
            $account = Ovpn::where('user_id',$trans->user_id)->where('used',1)->first();
            // it means that user updated his account. it's NOT a new account
            if($account !== null){
                $account->update(['expires_at'=> Carbon::now()->addMonths($trans->plan->month)]);
            }else{

                $account = Ovpn::where('plan_id',$trans->plan_id)->where('used',0)->first();
                $account->update(['used'=>1,'user_id'=>$trans->user_id,'expires_at'=>Carbon::now()->addMonths($trans->plan->month)]);
            }

        }

        $plan = DB::table('plans')->where('id',$trans->plan_id)->first();
        DB::commit();
        $char = Emoji::heavyCheckMark();
        $msg = [
            'chat_id' => $trans->user_id,
            'text' => "$char  با تشکر از خرید شما $char",
            'parse_mode' => 'HTML',
        ];
        $msg2 = [
            'chat_id' => $trans->user_id,
            'text' => ' نام کاربری '.$account->username,
            'parse_mode' => 'HTML',
        ];
        $msg3 = [
            'chat_id' => $trans->user_id,
            'text' => ' کلمه عبور '.$account->password,
            'parse_mode' => 'HTML',
        ];
        $msg4 = [
            'chat_id' => $trans->user_id,
            'text' => ' انقضا '.\Morilog\Jalali\Jalalian::now()->addMonths($plan->month)->format('%B %d، %Y'),
            'parse_mode' => 'HTML',
        ];
        $msg5 = [
            'chat_id' => $trans->user_id,
            'text' => ' شماره تراکنش '.$trans->trans_id,
            'parse_mode' => 'HTML',
        ];
        if($trans->email != null){
//
            Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=>$plan], function ($message) use($trans) {
                $message->from('support@joyvpn.xyz','JOY VPN');
                $message->to($trans->email);
                $message->subject('رسید پرداخت');
            });
        }else{

            $api = new \Kavenegar\KavenegarApi( env('SMS') );
            $sender = "10008000800600";
            $receptor = array($trans->phone);
            $message =
                'خرید از JOY VPN'
                . ' مبلغ ' . $trans->amount.' تومان '
                .' نام کاربری '. $account->username
                . ' کمه عبور '.$account->password
                . ' شماره تراکنش '.$trans->trans_id;
            $api->Send($sender,$receptor,$message);

        }

        Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=>$plan], function ($message) use($trans) {
            $message->from('support@joyvpn.xyz','JOY VPN');
            $message->to('sahand.mg.ne@gmail.com');
            $message->subject('رسید پرداخت');
        });

        $data = array($msg,$msg2,$msg3,$msg4,$msg5);
        $jsonData = json_encode($data);
        $ch = curl_init('https://vitamin-g.ir/api/hook?type=success');
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
