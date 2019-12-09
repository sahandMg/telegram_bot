<?php

namespace App\Http\Controllers;


use App\Zarrin;
use Illuminate\Http\Request;

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
        $code = $trans->code;
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


}
