<?php
namespace App;


use App\Accounts;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Morilog\Jalali\Jalalian;

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
        $data = array('MerchantID' => env('ZARRIN_TOKEN'),
            'Amount' => $amount,
            'CallbackURL' => 'http://pay.joyvpn.xyz/zarrin/callback',
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
                    $trans = new Transaction();
                    $trans->trans_id = 'Zarrin_' . strtoupper(uniqid());
                    $trans->status = 'unpaid';
                    $trans->amount = $amount;
                    $trans->authority = $result['Authority'];
                    $trans->username = $this->request['username'];
                    $trans->plan_id = $this->request['plan_id'];
                    $trans->user_id = $this->request['user_id'];
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
            if ($result['Status'] == '100') {

                $this->ZarrinPaymentConfirm($trans);

                return redirect()->route('RemotePaymentSuccess',['transid'=>$trans->trans_id]);

            } else {

                return redirect()->route('RemotePaymentCanceled', ['transid' => $trans->trans_id]);
            }
        }
    }
    private function ZarrinPaymentConfirm($trans)
    {

        $transactionId = $trans->trans_id;
        $orderID = $transactionId;

            // update created transaction record
        DB::beginTransaction();
            DB::connection('mysql')->table('transactions')->where('trans_id', $orderID)->update([
                'status' => 'paid'
            ]);
        $account = Accounts::where('plan_id',$trans->plan_id)->where('used',0)->first();
        $account->update(['used'=>1,'user_id'=>$trans->user_id]);
        $plan = DB::table('plans')->where('id',$trans->plan_id)->first();
        DB::commit();

        $msg = [
            'chat_id' => $trans->user_id,
            'text' => 'با تشکر از خرید شما',
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

    try{
        $data = array($msg,$msg2,$msg3,$msg4);
        $jsonData = json_encode($data);
        $ch = curl_init('http://vitamin-g.ir/api/hook');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        curl_exec($ch);
    }catch (\Exception $exception){

    }

        if($trans->email != null){

            Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=>$plan], function ($message) use($trans) {
                $message->from('support@joyvpn.xyz');
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
                . ' کمه عبور '.$account->password;
            $api->Send($sender,$receptor,$message);

        }


        Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=>$plan], function ($message) use($trans) {
            $message->from('support@joyvpn.xyz');
            $message->to('sahand.mg.ne@gmail.com');
            $message->subject('رسید پرداخت');
        });

    }

}
