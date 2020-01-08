<?php

namespace App\Http\Controllers;


use App\Accounts;
use App\Transaction;
use App\Zarrin;
use App\Zarrin_Tamdid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function ZarrinCallback(Request $request){


            $zarrin = new Zarrin($request->all());

            return $zarrin->verify();

    }

// gets transid
    public function successPayment($transid){
        $trans = DB::connection('mysql')->table('transactions')->where('trans_id',$transid)->first();
        if(is_null($trans)){
            return 'تراکنش نامعتبر';
        }
        $code = $trans->trans_id;
        return view('success',compact('code'));
    }

    public function failedPayment($transid){
        $trans = DB::connection('mysql')->table('transactions')->where('trans_id',$transid)->first();
        if(is_null($trans)){
            return 'تراکنش نامعتبر';
        }
        $code = $trans->trans_id;
        return view('failed',compact('code'));
    }

    public function tamdid(Request $request){

        $request = $request->all();
        if(!isset($request['trans_id']) || !isset($request['usr']) || !isset($request['id'])){

            return 'لینک نامعتبر';
        }
        $trans = Transaction::where('trans_id',$request['trans_id'])->first();

        if(is_null($trans)){

            return 'لینک نامعتبر';
        }
        if(Carbon::now() > Carbon::parse($trans->account->expires_at)){
            return 'این حساب غیر فعال شده است. لطفااز طریق @JoyVpn_bot درخواست حساب جدید دهید';
        }
        $request['amount'] = $trans->plan->price;
        $request['type'] = 'tamdid';
        $request['user_id'] = $trans->user_id;
        $request['plan_id'] = $trans->plan_id;
        $request['username'] = $trans->username;
        $request['service'] = $trans->service;
        if(!is_null($trans->phone)){
            $request['phone'] = $trans->phone;
        }else{
            $request['email'] = $trans->email;
        }
        $zarrin = new Zarrin_Tamdid($request);
        $result = $zarrin->create();
        if($result != 404){

            return redirect('https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]);
        }
        else{
            return 'اشکالی در پرداخت پیش آمده';
        }

    }
    public function ZarrinCallbackTamdid(Request $request){


        $zarrin = new Zarrin_Tamdid($request->all());

        return $zarrin->verify();

    }


}
