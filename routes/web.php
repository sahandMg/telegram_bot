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
Route::get('zarrin/callback/tamdid', 'PaymentController@ZarrinCallbackTamdid')->name('ZarrinCallbackTamdid');
Route::get('payment/success/{transid}',['as'=>'RemotePaymentSuccess','uses'=>'PaymentController@successPayment']);
Route::get('payment/canceled/{transid}',['as'=>'RemotePaymentCanceled','uses'=>'PaymentController@FailedPayment']);
Route::get('import',['as'=>'importAccount','uses'=>'AccountController@index']);
Route::post('import',['as'=>'importAccount','uses'=>'AccountController@post_index']);
Route::get('af/{id}','AffiliateController@landing');
Route::get('affiliate','AffiliateController@sendNotif');

Route::post('getfile',function (\Illuminate\Http\Request $request){

    dd($request->all());

})->name('getfile');

Route::get('test',function (){

//    $userIds = Accounts::where('used',1)->get()->pluck('user_id')->toArray();
//    $userIds = array_values(array_unique($userIds));
//    dd($userIds);
//    $inviterShares = Accounts::get()->sum('password');
//    dd($inviterShares);
//    $trans = Transaction::find(24);
//    $plan = \App\Plan::find(1);
//    $account = Accounts::find(2);
//    $textMsg =  'یادآوری تمدید حساب JOY VPN.'
//        . ' کاربر گرامی، تنها 1 روز از اعتبار حساب شما باقی مانده. جهت تمدید حساب با نام ' . $account->username
//        . ' با قیمت ' . $trans->plan->price . ' تومان '
//        . ' به لینک زیر مراجعه فرمایید. '
//        . "http://pay.joyvpn.xyz/tamdid?usr=$account->username&id=$account->user_id&trans_id=$trans->trans_id";
//    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
//    $msg = [
//        'chat_id' => 83525910,
//        'text' => $textMsg,
//        'parse_mode' => 'HTML',
//    ];
//    $telegram->sendMessage($msg);
//    dd(Carbon::now()->addMonth($account->plan->month),$account->plan->month);
//    Mail::send('reminder', ['account' => $account, 'trans' => $trans,'plan'=> $plan], function ($message) use($trans) {
//        $message->to('s23.moghadam@gmail.com');
//        $message->subject('یادآوری تمدید حساب');
//    });

//    Mail::send('welcome',[],function($message){
//        $message->to('s23.moghadam@gmail.com');
////        $message->to('test-wbuck@mail-tester.com');
//        $message->subject(Emoji::globeShowingAmericas().' !!دنیای بدون مرز!! '.Emoji::globeShowingAmericas());
//    });

});


Route::get('comment',function (){

    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
//    Accounts::where('user_id',);
    if(!isset($_GET['id'])){
        return 'enter chat id';
    }else{
        $id = $_GET['id'];
    }
    $chat_id = $id;
    $options = [
        array($telegram->buildInlineKeyboardButton(Emoji::okHandMediumLightSkinTone() .' خوبه تمدید می‌کنم '.Emoji::okHandMediumLightSkinTone(),'','y')),
        array($telegram->buildInlineKeyboardButton(Emoji::angryFace() .' نه راضی نیستم '.Emoji::angryFace(),'','n'))
    ];
    $msg = [
        'chat_id' => $chat_id,
        'text' => Emoji::thinkingFace().' از خدمات ما راضایت دارید؟ '.Emoji::thinkingFace(),
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->buildInlineKeyboard($options),
    ];

    $telegram->sendMessage($msg);
    \App\Jobs\Activities::dispatch($chat_id,'از خدمات ما راضایت دارید؟');

});

Route::get('support',function (){

    if(!isset($_GET['id'])){
        return 'enter chat id';
    }else{
        $id = $_GET['id'];
    }
    $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
//    Accounts::where('user_id',);

    $chat_id = $id;
    $options = [
        array($telegram->buildInlineKeyboardButton(' پشتیبانی ','https://t.me/JoyVpn_Support')),
    ];
    $msg = [
        'chat_id' => $chat_id,
        'text' => Emoji::loudspeaker().Emoji::loudspeaker().' درصورت وجود هرگونه مشکل و یا سوال با پشتیبانی در ارتباط باشید ',
        'parse_mode' => 'HTML',
        'reply_markup' => $telegram->buildInlineKeyboard($options),
    ];

//    $telegram->sendMessage($msg);

    \App\Jobs\Activities::dispatch($chat_id,' درصورت وجود هرگونه مشکل و یا سوال با پشتیبانی در ارتباط باشید');
});

Route::get('tamdid','PaymentController@tamdid')->name('tamdid');
Route::post('adv','MailController@sendMail')->name('adv');
Route::get('adv','MailController@get_sendMail')->name('adv');