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
use App\Jobs\sendNotif;
use App\Jobs\TelegramNotification;
use App\Num2En;
use App\Server;
use App\ShortLink;
use App\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


Route::get('zarrin/callback', 'PaymentController@ZarrinCallback')->name('ZarrinCallback');
Route::get('zarrin/callback/tamdid', 'PaymentController@ZarrinCallbackTamdid')->name('ZarrinCallbackTamdid');
Route::get('zarrin/callback/ren','PaymentController@ZarrinCallbackRenew');
Route::get('payment/success/{transid}',['as'=>'RemotePaymentSuccess','uses'=>'PaymentController@successPayment']);
Route::get('payment/canceled/{transid}',['as'=>'RemotePaymentCanceled','uses'=>'PaymentController@FailedPayment']);
Route::get('import',['as'=>'importAccount','uses'=>'AccountController@index']);
Route::post('import',['as'=>'importAccount','uses'=>'AccountController@post_index']);
Route::get('af/{id}','AffiliateController@landing');
Route::get('affiliate','AffiliateController@sendNotif');

Route::get('ren','PaymentController@loyalUser');
Route::get('tamdid','PaymentController@tamdid')->name('tamdid');
Route::post('adv','MailController@sendMail')->name('adv');
Route::get('adv','MailController@get_sendMail')->name('adv');
Route::post('getfile',function (\Illuminate\Http\Request $request){

    dd($request->all());

})->name('getfile');

Route::get('send',function (){

//    $mails = [
//        's24.moghadam@gmail.com','s24.moghadam@yahoo.com',
//        's25.moghadam@yahoo.com','s26.moghadam@yahoo.com',
//        's27.moghadam@yahoo.com','s28.moghadam@yahoo.com',
//        's25.moghadam@gmail.com','s26.moghadam@gmail.com',
//        's27.moghadam@gmail.com','s28.moghadam@gmail.com',
//        's29.moghadam@gmail.com','s30.moghadam@gmail.com',
//        's31.moghadam@gmail.com','s32.moghadam@gmail.com',
//        's33.moghadam@gmail.com','newton.greens.ng@gmail.com'
////            's23.moghadam@gmail.com'
//    ];
//////    $mails = ['s23.moghadsadsadasad@gmail.com'];
//    for($i=0;$i<50;$i++){
//////    return view('welcome');
//        Mail::send('welcome',[],function($message)use($mails,$i){
//            $message->to($mails[rand(0,15)]);
//            $message->from('support@joyvpn.xyz');
//            $message->subject(Emoji::globeShowingAmericas().' !!دنیای بدون مرز!! '.Emoji::globeShowingAmericas());
//        });
////        sleep(0.5);
//    }


//    $account = Accounts::where('user_id','687738118')->first();
//    $target_date = Carbon::parse($account->expires_at);
//    $diff = Carbon::now()->diffInHours($target_date);
//    dd($diff);
//);
//    $textMsg =
//        'یادآوری تمدید حساب JOY VPN.'
//        .' کاربر گرامی، تنها ۷ روز از اعتبار حساب شما باقی مانده. جهت تمدید حساب با نام '.$account->username
//        .' با قیمت 20000. تومان '
//        .' به لینک مراجعه کنید '
//        ."http://pay.joyvpn.xyz/tamdid?usr=$account->username&id=$account->user_id&trans_id=dsahdashkdewq";
    $acc = Accounts::where('used',1)->get();
    foreach($acc as $item){
        $textMsg = Emoji::redCircle().Emoji::redCircle().' همراهان گرامی، آدرس جدید ربات سرویس JOY VPN به  @JOY_VPN_bot تغییر پیدا کرده است، لطفا از این طریق برای دریافت سرویس اقدام نمایید. باتشکر';
        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $msg = [
            'chat_id' => $item->user_id,
            'text' => $textMsg,
            'parse_mode' => 'HTML',
        ];
        $telegram->sendMessage($msg);
    }

//    $trans = Transaction::find(108);
//    $account = Accounts::find(2);
//    sendNotif::dispatch($trans,$account);
//        dd(Cache::get("tg"));
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

Route::get('senddd',function(){

    dd(Cache::get('tg'));
});

Route::get('loyal-notif',function (){

    $uid = 83525910;
    $text = "
        🔥<b> تخفیف ویژه برای خانواده JOY VPN🔥</b>

💥 ۲۰ درصد تخفیف روی تمامی طرح‌ها برای اعضای  JOY VPN

✅  اکانت ۱ ماهه با تخفیف ۲۰ درصدی، به قیمت ۸۰۰۰ تومان😎
 لینک خرید👇👇
🌍<a href='http://pay.joyvpn.xyz/ren?uid=$uid&pid=1 '>http://joyvpn.xyz/ren</a>
✅  اکانت ۳ ماهه  با تخفیف ۲۰ درصدی، به قیمت ۱۶۰۰۰ تومان🤑
لینک خرید 👇👇
🌏<a href='http://pay.joyvpn.xyz/ren?uid=$uid&pid=2 '>http://joyvpn.xyz/ren</a>
        ";
    $msg = [
        'chat_id' => $uid,
        'text' => $text,
        'parse_mode' => 'HTML',
    ];
    TelegramNotification::dispatch($msg);
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
        array($telegram->buildInlineKeyboardButton('✅ خوبه، نیازهام رو برطرف می‌کنه. در کل راضیم ✅','','y')),
        array($telegram->buildInlineKeyboardButton('🛑  نه راضی نیستم! 🛑
⚠️ لطفا دلایل نارضایتی خود را با JoyVpn_Support@ در میان بگذارید','','n'))
    ];
    $msg = [
        'chat_id' => $chat_id,
        'text' => Emoji::thinkingFace().'<b> از خدمات ما راضایت دارید؟ </b>'.Emoji::thinkingFace(),
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

    $telegram->sendMessage($msg);

    \App\Jobs\Activities::dispatch($chat_id,' درصورت وجود هرگونه مشکل و یا سوال با پشتیبانی در ارتباط باشید');
});
