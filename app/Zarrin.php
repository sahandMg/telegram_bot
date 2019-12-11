<?php
namespace App;


use App\Accounts;
use App\Jobs\sendNotif;
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
    public function ZarrinPaymentConfirm($trans)
    {
        sendNotif::dispatch($trans);

    }

}
