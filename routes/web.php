<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Transaction;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


Route::get('zarrin/callback', 'PaymentController@ZarrinCallback')->name('ZarrinCallback');
Route::get('payment/success/{transid}',['as'=>'RemotePaymentSuccess','uses'=>'PaymentController@successPayment']);
Route::get('payment/canceled/{transid}',['as'=>'RemotePaymentCanceled','uses'=>'PaymentController@FailedPayment']);

Route::get('test',function (){

    $trans = Transaction::find(2);
    $account = \App\Accounts::find(2);
    $plan = \App\Plan::find(2);
    Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=>$plan], function ($message) use($trans) {
        $message->from('support@joyvpn.xyz');
        $message->to($trans->email);
        $message->subject('رسید پرداخت');
    });

    return view('invoice');
});