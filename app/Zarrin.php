<?php
namespace App;


use App\Accounts;
use App\Jobs\sendNotif;
use App\Jobs\TelegramNotification;
use App\Repo\IpFinder;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Morilog\Jalali\Jalalian;
use Spatie\Emoji\Emoji;

class Zarrin
{

    public $request;
    protected $connection = 'mysql';

    public function __construct(array $request)
    {
        $this->request = $request;
    }
    public function create(){


        $amount = $this->request['amount'];
        $callback = 'http://pay.joyvpn.xyz/zarrin/callback';
        $data = array('MerchantID' => env('ZARRIN_TOKEN'),
            'Amount' => $amount,
            'CallbackURL' => $callback,
            'Description' => 'خرید سرویس شبکه شخصی مجازی');
        $jsonData = json_encode($data);
        $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        if ($err) {
            return 404;
        } else {
            if ($result["Status"] == '100' ) {

                DB::beginTransaction();
                $cache = CacheData::where('user_id',$this->request['user_id'])->where('closed',0)->first();
                    $trans = new Transaction();
                    $trans->trans_id = 'Zarrin_' . strtoupper(uniqid());
                    $trans->status = 'unpaid';
                    $trans->amount = $amount;
                    $trans->authority = $result['Authority'];
                    $trans->username = $this->request['username'];
                    $trans->plan_id = $this->request['plan_id'];
                    $trans->user_id = $this->request['user_id'];
//                    $trans->service = Cache::get($this->request['user_id'].'_service')['value'];
                    $trans->service = $cache->service;
                    if (isset($this->request['email'])) {

                        $trans->email = $this->request['email'];
                    } else {

                        $trans->phone = $this->request['phone'];
                    }
                    $trans->save();
                DB::commit();
                return $result;
            } else {
                return 404;
            }
        }

    }

    public function verify(){
        $transactionId = $this->request['Authority'];
        $trans = Transaction::where('authority',$transactionId)->first();
        if(is_null($trans)){
            return 'کد تراکنش نادرست است';
        }
        if($trans->status == 'paid'){
            return 'تراکنش تکراری است';
        }


        $data = array('MerchantID' => env('ZARRIN_TOKEN'), 'Authority' => $transactionId, 'Amount'=>$trans->amount);
        $jsonData = json_encode($data);
        $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            DB::beginTransaction();
            CacheData::where('user_id',$trans->user_id)->where('closed',0)->first()->update(['closed'=>1]);
            DB::commit();
            if ($result['Status'] == '100') {

                $this->ZarrinPaymentConfirm($trans);

                return redirect()->route('RemotePaymentSuccess',['transid'=>$trans->trans_id]);

            } else {
                DB::beginTransaction();
                $trans->update(['status'=>'canceled']);
                DB::commit();
                $char = Emoji::redCircle();
                $msg = [
                    'chat_id' => $trans->user_id,
                    'text' => " $char $char پرداخت شما ناموفق بود. شماره تراکنش : $trans->trans_id",
                    'parse_mode' => 'HTML',
                ];
                $data = array($msg);
                $jsonData = json_encode($data);
                $ch = curl_init('https://vitamin-g.ir/api/hook?type=canceled');
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

                return redirect()->route('RemotePaymentCanceled', ['transid' => $trans->trans_id]);
            }
        }
    }
    private function ZarrinPaymentConfirm($trans)
    {
        // if($trans->service == 'cisco'){
        //     $account = Accounts::where('user_id',$trans->user_id)->where('plan_id','!=',3)->where('used',1)->first();
        //     // it means that user updated his account. it's NOT a new account
        //     if($account !== null){
        //         $account->update(['expires_at'=> Carbon::now()->addMonths($trans->plan->month)]);
        //     }else{


        // }

        // }elseif ($trans->service == 'openvpn'){
        //     $account = Ovpn::where('user_id',$trans->user_id)->where('used',1)->first();
        //     // it means that user updated his account. it's NOT a new account
        //     if($account !== null){
        //         $account->update(['expires_at'=> Carbon::now()->addMonths($trans->plan->month)]);
        //     }else{

        //         $account = Ovpn::where('plan_id',$trans->plan_id)->where('used',0)->first();
        //         $account->update(['used'=>1,'user_id'=>$trans->user_id,'expires_at'=>Carbon::now()->addMonths($trans->plan->month)]);
        //     }

        // }
        DB::beginTransaction();
        DB::connection('mysql')->table('transactions')->where('trans_id', $trans->trans_id)->update([
            'status' => 'paid'
        ]);
        $account = Accounts::where('plan_id',$trans->plan_id)->where('used',0)->first();
        $account->update(['used'=>1,'user_id'=>$trans->user_id,'expires_at'=>Carbon::now()->addMonths($trans->plan->month)]);
        $trans->update(['account_id'=>$account->id]);

//  =================  Affiliation Part =================

        $ip = IpFinder::find();
        $affiliationBuy = Affiliate::where('invitee',$ip)->where('done',0)->first();
        if(!is_null($affiliationBuy)){
            $affiliationBuy->update(['invitee_id'=>$trans->user_id,'done'=>1]);
            $inviterShares = Affiliate::where('inviter',$affiliationBuy->inviter)->get()->sum('done');
            if($inviterShares == 3){

                $msg = [
                    'chat_id' => $affiliationBuy->inviter,
                    'text' => Emoji::fire().'تبریک! فقط ۲ کاربر تا حساب رایگان ۱ ماهه فاصله دارید'.Emoji::fire()
                ];
                TelegramNotification::dispatch($msg);
            }
            elseif ($inviterShares == 8){
                $msg = [
                    'chat_id' => $affiliationBuy->inviter,
                    'text' => Emoji::fire().'تبریک! فقط ۲ کاربر تا حساب رایگان ۱ ماهه فاصله دارید'.Emoji::fire()
                ];
                TelegramNotification::dispatch($msg);
            }
            elseif ($inviterShares == 5){
              $this->affiliateReward5($affiliationBuy);
            }
            elseif ($inviterShares == 10){
                $this->affiliateReward10($affiliationBuy);
            }
        }
        DB::commit();
        sendNotif::dispatch($trans,$account);

    }

    private function affiliateReward5($affiliationBuy){

        $newaccount = Accounts::where('plan_id',1)->where('used',0)->first();
        $newaccount->update(['used'=>1,'user_id'=>$affiliationBuy->inviter,'expires_at'=>Carbon::now()->addMonths(1)]);
        $newTrans = new Transaction();
        $newTrans->trans_id = 'Affiliate_'.strtoupper(uniqid());
        $newTrans->user_id = $affiliationBuy->inviter;
        $newTrans->plan_id = 1;
        $newTrans->amount = 0;
        $newTrans->authority = 'Affiliate';
        $newTrans->account_id = $newaccount->id;
        $newTrans->status = 'paid';
        $newTrans->service = 'cisco';
        $inviterLastPurchase = Transaction::where('user_id',$affiliationBuy->inviter)->where('status','paid')->first();
        $newTrans->username = $inviterLastPurchase->username == null?'someone':$inviterLastPurchase->username;
        $newTrans->save();

        $msg = [
            'chat_id' => $affiliationBuy->inviter,
            'text' => Emoji::fire().Emoji::fire().'تبریک! شما ۵ کاربر را به joy vpn ملحق کردید'.Emoji::flexedBicepsMediumLightSkinTone().' حساب رایگان ۱ ماهه شما فعال شد. فقط ۵ نفر تا حساب ۳ ماهه فاصله دارید!'.Emoji::starStruck()
        ];
        TelegramNotification::dispatch($msg);

        $msg2 = [
            'chat_id' => $affiliationBuy->inviter,
            'text' => ' نام کاربری '.$newaccount->username,
            'parse_mode' => 'HTML',
        ];
        TelegramNotification::dispatch($msg2);
        $msg3 = [
            'chat_id' => $affiliationBuy->inviter,
            'text' => ' کلمه عبور '.$newaccount->password,
            'parse_mode' => 'HTML',
        ];
        TelegramNotification::dispatch($msg3);
        $msg4 = [
            'chat_id' => $affiliationBuy->inviter,
            'text' => ' انقضا '.\Morilog\Jalali\Jalalian::now()->addMonths(1)->format('%B %d، %Y'),
            'parse_mode' => 'HTML',
        ];
        TelegramNotification::dispatch($msg4);

    }
    private function affiliateReward10($affiliationBuy){

        $newTrans = Transaction::where('user_id',$affiliationBuy->inviter)->where('authority','Affiliate')->first();
        $newaccount = $newTrans->account;
        $newaccount->update(['expires_at'=>Carbon::parse($newaccount->expires_at)->addMonths(3)]);
        $msg = [
            'chat_id' => $affiliationBuy->inviter,
            'text' => Emoji::fire().Emoji::fire().'تبریک! شما ۱۰ کاربر را به joy vpn ملحق کردید'.Emoji::flexedBicepsMediumLightSkinTone().' حساب شما به حساب رایگان ۳ ماهه ارتقا پیدا کرد'.Emoji::smilingFaceWithHeartEyes()
        ];
        TelegramNotification::dispatch($msg);

    }

}
