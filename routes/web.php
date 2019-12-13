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
use App\Accounts;
use App\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


Route::get('zarrin/callback', 'PaymentController@ZarrinCallback')->name('ZarrinCallback');
Route::get('payment/success/{transid}',['as'=>'RemotePaymentSuccess','uses'=>'PaymentController@successPayment']);
Route::get('payment/canceled/{transid}',['as'=>'RemotePaymentCanceled','uses'=>'PaymentController@FailedPayment']);

Route::get('run',function (){

    echo shell_exec("./com.sh sahand");
});
Route::get('test',function (){
    $trans = Transaction::find(24);
    $lastAccount = Accounts::where('user_id',$trans->user_id)->where('used',1)->first();
    dd(Carbon::now()->diffInDays(Carbon::parse($lastAccount->updated_at)));
    $lastAccount->delete();
    // it means that user updated his account. it's NOT a new account
    dd(Carbon::now()->diffInDays(Carbon::parse($lastAccount->updated_at)));
    Emoji::CHARACTER_GRINNING_FACE;
    echo  Emoji::largeOrangeDiamond();
    echo  Emoji::smilingFaceWithSmilingEyes();

});