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
use App\Num2En;
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

Route::get('/',function (\Illuminate\Http\Request $request){

   echo '
    <form action="https://api.telegram.org/bot844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8/sendDocument" method="post" enctype="multipart/form-data">
        <input type="file" name="document">
        <button type="submit">Send</button>
    </form>
   ';

});
Route::post('getfile',function (\Illuminate\Http\Request $request){

    dd($request->all());

})->name('getfile');

Route::get('run',function (){

    echo shell_exec("./com.sh sahand");
});
Route::get('test',function (){

    dd(Carbon::now());
    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
    $msg = [
        'chat_id'=> '83525910',
        'document'=> 'http://vitamin-g.ir/clients/pezhman.ovpn'
    ];
    $telegram->sendDocument($msg);
    $trans = Transaction::find(24);
    $lastAccount = Accounts::where('user_id',$trans->user_id)->where('used',1)->first();
    dd(strlen('۱'));
    dd(Carbon::now()->diffInDays(Carbon::now()->addDays(20)));
    $lastAccount->delete();
    // it means that user updated his account. it's NOT a new account
    dd(Carbon::now()->diffInDays(Carbon::parse($lastAccount->updated_at)));
    Emoji::CHARACTER_GRINNING_FACE;
    echo  Emoji::largeOrangeDiamond();
    echo  Emoji::smilingFaceWithSmilingEyes();

});