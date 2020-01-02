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
use App\Server;
use App\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


Route::get('zarrin/callback', 'PaymentController@ZarrinCallback')->name('ZarrinCallback');
Route::get('payment/success/{transid}',['as'=>'RemotePaymentSuccess','uses'=>'PaymentController@successPayment']);
Route::get('payment/canceled/{transid}',['as'=>'RemotePaymentCanceled','uses'=>'PaymentController@FailedPayment']);
Route::get('import',['as'=>'importAccount','uses'=>'AccountController@index']);
Route::post('import',['as'=>'importAccount','uses'=>'AccountController@post_index']);



Route::post('getfile',function (\Illuminate\Http\Request $request){

    dd($request->all());

})->name('getfile');

Route::get('run',function (){

    phpinfo();
});
Route::get('test',function (){
    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
    $trans = new Transaction();
    $trans->trans_id = 'free';
    $trans->user_id = 212121;
    $trans->plan_id = 3;
    $trans->amount = 0;
    $trans->authority = 'JOYVPN_FREE_ACCOUNT';
    $trans->username = 321312;
    $trans->service = 'cisco';
    $trans->status = 'paid';
    $trans->save();
    $plan = \App\Plan::find(3);
    $account = Accounts::find(101);
    dd($plan);
    Mail::send('invoice', ['account' => $account, 'trans' => $trans,'plan'=> $plan], function ($message) use($trans) {
        $message->from('support@joyvpn.xyz','JOY VPN');
        $message->to('sahand.mg.ne@gmail.com');
        $message->subject('رسید پرداخت');
    });
});

Route::get('comment',function (){

    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
//    Accounts::where('user_id',);

    $chat_id = 83525910;
    $options = [
        array($telegram->buildInlineKeyboardButton(Emoji::okHandMediumLightSkinTone() .' خوبه تمدید می‌کنم '.Emoji::okHandMediumLightSkinTone(),'','y')),
        array($telegram->buildInlineKeyboardButton(Emoji::angryFace() .' نه راضی نیستم '.Emoji::angryFace(),'','n'))
    ];
    $msg = [
        'chat_id' => $chat_id,
        'text' => Emoji::thinkingFace().' از خدمات ما راضی هستید؟ '.Emoji::thinkingFace(),
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->buildInlineKeyboard($options),
    ];

    $telegram->sendMessage($msg);

});