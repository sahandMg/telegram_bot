<?php

namespace App\Http\Controllers;

use App\Accounts;
use App\CacheData;
use App\Comment;
use App\Num2En;
use App\Ovpn;
use App\Plan;
use App\Server;
use App\Transaction;
use App\Zarrin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repo\TelegramErrorLogger;
use \Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;
use Telegram\Bot\Api;
class TelegramCommandController extends Controller
{
//    handles incoming messages from bot
    /**
     * @param Request $request
     */
    public $telegram;
    public $cache;
    public function __construct()
    {
        DB::beginTransaction();
        $this->telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $this->cache = CacheData::where('user_id',$this->telegram->ChatID())->where('closed',0)->first();
        if(is_null($this->cache)){
            $cache = new CacheData();
            $cache->username = $this->telegram->Username();
            $cache->user_id = $this->telegram->ChatID();
            $cache->user_id = $this->telegram->ChatID();
            $cache->save();
            $this->cache = $cache;
        }
        DB::commit();
    }

    public function incoming(Request $request)
    {
        // $telegram = new Api('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');
        $dic = ['/start','refused','restart','لیست تراکنش‌ها','تماس با ما',
            'شروع مجدد','cisco','openvpn','open','y','n','شروع','پشتیبانی'
        ];
        $data = '';
        $text = '';
        $telegram =  $this->telegram;
        $tgResp = $request->all();
        if(isset($tgResp['message'])){
            $userId = $tgResp['message']['from']['id'];
            $username = $tgResp['message']['from']['first_name'];
        }else{
            $userId = $tgResp['callback_query']['from']['id'];
            $username = $tgResp['callback_query']['from']['first_name'];
        }

        // $isBot = $tgResp['message']['from']['is_bot'];
        $text = $this->telegram->Text();
        $chat_id = $this->telegram->ChatID();

        Cache::put("tg",$tgResp,10000);
        /*
         * ========= START BTN CLICKED ===========
         */
        if ($text == '/start'){

            $this->startBtn();

        }

        /*
       * ========= ReSTART BTN CLICKED ===========
       */
        elseif ($text == 'شروع مجدد'){

            $this->restartBtn();

            return 200;

        }


        /*
        * ========= Transaction List BTN CLICKED ===========
        */

        elseif($text == 'لیست تراکنش‌ها'){

            $this->transactionList();
            return 200;
        }

        /*
       * ========= Contact  BTN CLICKED ===========
       */

        elseif($text == 'تماس با ما'){

            $this->contactUs();
        }


        /*
         * ========= GLASSY BTN CLICKED ===========
         */
        elseif (isset($tgResp['callback_query'])) {
            $data = $tgResp['callback_query']['data'];
            $this->glassyBtn($data);
        }
//        if(strpos($text,'@') || (strlen($text) == 11 && is_numeric($text)) || Cache::get($chat_id) !== null){
        elseif($this->cache->closed == 0){
//        elseif(Cache::get($chat_id) !== null){
                if(!isset($tgResp['callback_query'])){

                    if(strpos($text,'@') || (strlen($text) == 11)){
                        $pass = 1;
                    }else{
                        $msg = [
                            'chat_id' => $chat_id,
                            'text' => 'ایمیل و یا شماره موبایل را نادرست وارد کردید',
                            'parse_mode' => 'HTML',
                        ];
                        $telegram->sendMessage($msg);
                    }
                    if(isset($pass)) {
//                        $cached = Cache::get($chat_id);
                        $cached = $this->cache;

//                        Cache::forget($chat_id);

                        if ($cached->plan_id == 1) {

                            $plan = Plan::where('id',1)->first();
                            $price = $plan->price;
                            if (strpos($text, '@')) {
                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => $price, 'email' => $text, 'plan_id' => $plan->id]);
                            } else {
                                $text = Num2En::en($text);
                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => $price, 'phone' => $text, 'plan_id' => $plan->id]);
                            }
                            $msg = [
                                'chat_id' => $chat_id,
                                'text' => 'لطفا منتظر بمانید',
                                'parse_mode' => 'HTML',
                            ];
                            $telegram->sendMessage($msg);

                            $result = $zarrin->create();
                            if($result != 404){
                                $option = [
                                    array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت', 'https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]), $telegram->buildInlineKeyboardButton('انصراف', '', 'refused'))
                                ];
                                $msg_text = " مبلغ قابل پرداخت : $price تومان ";
                                $msg = [
                                    'chat_id' => $chat_id,
                                    'text' => $msg_text,
                                    'parse_mode' => 'HTML',
                                    'reply_markup' => $telegram->buildInlineKeyboard($option)
                                ];
                                $telegram->sendMessage($msg);
                                return 200;
                            }else{
                                $msg = [
                                    'chat_id' => $chat_id,
                                    'text' => 'مشکلی در ارتباط با درگاه پیش آمده است. لطفا دوباره تلاش کنید',
                                    'parse_mode' => 'HTML',
                                ];
                                $telegram->sendMessage($msg);

                                return 200;
                            }

                        } elseif ($cached->plan_id == 2) {
                            $plan = Plan::where('id',2)->first();
                            $price = $plan->price;
                            if (strpos($text, '@')) {

                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => $price, 'email' => $text, 'plan_id' => $plan->id]);
                            } else {
                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => $price, 'email' => $text, 'plan_id' => $plan->id]);
                            }
                            $msg = [
                                'chat_id' => $chat_id,
                                'text' => 'لطفا منتظر بمانید',
                                'parse_mode' => 'HTML',
                            ];
                            $telegram->sendMessage($msg);
                            $result = $zarrin->create();
                            if($result != 404){
                                $option = [
                                    array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت', 'https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]), $telegram->buildInlineKeyboardButton('انصراف', '', 'refused'))
                                ];
                                $msg_text = " مبلغ قابل پرداخت : $price تومان ";
                                $msg = [
                                    'chat_id' => $chat_id,
                                    'text' => $msg_text,
                                    'parse_mode' => 'HTML',
                                    'reply_markup' => $telegram->buildInlineKeyboard($option)
                                ];
                                $telegram->sendMessage($msg);
                            }else{
                                $msg = [
                                    'chat_id' => $chat_id,
                                    'text' => 'مشکلی در ارتباط با درگاه پیش آمده',
                                    'parse_mode' => 'HTML',
                                ];
                                $telegram->sendMessage($msg);
                                return 200;
                            }
                        }
                    }
                }


            }


    }

    private function startBtn(){
        $chat_id = $this->telegram->ChatID();
        $telegram =  $this->telegram;
        $options = $this->mainKeyBoard();
        $but = [
            array($telegram->buildInlineKeyboardButton('شروع','','/start')),
            array($telegram->buildInlineKeyboardButton('لیست سرورها','','server_list')),
            array($telegram->buildInlineKeyboardButton('تعرفه‌ها','','pricing')),
            array($telegram->buildInlineKeyboardButton('پشتیبانی','https://t.me/joyVpn_Support')),
        ];
        $msg = [
            'chat_id' => $chat_id,
            'text' => ' از حسن انتخاب شما کمال تشکر را داریم. برای خرید حساب روی سرویس مورد نظر کلیک کنید ',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];

        $telegram->sendMessage($msg);

    }

    private function restartBtn(){

        $chat_id = $this->telegram->ChatID();
        $telegram =  $this->telegram;
       $options = $this->mainKeyBoard();
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'جهت خرید روی سرویس مورد‌نظر کلیک کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];

        $telegram->sendMessage($msg);
    }

    private function mainKeyBoard(){
        $telegram =  $this->telegram;
        $options = [
            array($telegram->buildInlineKeyBoardButton(Emoji::largeOrangeDiamond().' سرویس Cisco '.Emoji::largeOrangeDiamond(),"",'cisco')),
//            array($telegram->buildInlineKeyBoardButton(Emoji::largeOrangeDiamond().' سرویس OpenIR '.Emoji::largeOrangeDiamond(),"",'open')),
        ];
        return $options;
    }
    private function glassyBtn($data){

        $telegram =  $this->telegram;
        $chat_id = $this->telegram->ChatID();
        DB::beginTransaction();
        if($data == '1'){
//            Cache::put("$chat_id",['id'=>$chat_id,'value'=>1],1000);
            $this->cache->update(['plan_id'=> 1]);
           $this->planRegistration(1,$telegram);
        }elseif($data == '3'){
//            Cache::put("$chat_id",['id'=>$chat_id,'value'=>3],1000);
            $this->cache->update(['plan_id'=> 2]);
            $this->planRegistration(2,$telegram);
        }elseif($data == '0'){
//            Cache::put("$chat_id",['id'=>$chat_id,'value'=>0],1000);
            $this->cache->update(['plan_id'=> 3]);
            $msg_text = 'حساب تست ۳ روز اعتبار خواهد داشت';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
//            $service = Cache::get($chat_id.'_service')['value'];
            $service = $this->cache->service;
            $freeAccount = null;
            if($service == 'cisco'){
                $freeAccount = Accounts::where('user_id',$chat_id)->where('plan_id',3)->first();
                if(is_null($freeAccount)){

                    $account = Accounts::where('plan_id',3)->where('used',0)->first();
//                    DB::beginTransaction();
                    $account->update(['used' => 1,'user_id' => $chat_id,'expires_at'=> Carbon::now()->addDays(3)]);
//                    DB::commit();
                    $telegram->sendMessage($msg);
                    $msg_text = ' username: '.$account->username;
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);
                    $msg_text = 'password : '.$account->password;
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);

                    $option2 = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => 'جهت خرید مجدد، کلیک کنید',
                        'parse_mode' => 'HTML',
                        'reply_markup' => $telegram->buildInlineKeyboard($option2),
                    ];
                    $telegram->sendMessage($msg);

                    $this->sendNotifToAdmin($telegram,$account);
                }else{
                    $msg_text = 'شما پیش‌ از این حساب رایگان را دریافت کرده‌اید';
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);
                    $option2 = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => 'جهت خرید مجدد، کلیک کنید',
                        'parse_mode' => 'HTML',
                        'reply_markup' => $telegram->buildInlineKeyboard($option2),
                    ];
                    $telegram->sendMessage($msg);

                }
            }
            elseif ($service == 'openvpn'){
                $freeAccount = Ovpn::where('user_id',$chat_id)->first();
                if(is_null($freeAccount)){

                    $account = Ovpn::where('plan_id',3)->where('used',0)->first();
//                    DB::beginTransaction();
                    $account->update(['used' => 1,'user_id' => $chat_id,'expires_at'=> Carbon::now()->addDays(3)]);
//                    DB::commit();
                    $telegram->sendMessage($msg);
                    $msg_text = ' username: '.$account->username;
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);
                    $msg_text = 'password : '.$account->password;
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);

                    $option2 = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => 'جهت خرید مجدد، کلیک کنید',
                        'parse_mode' => 'HTML',
                        'reply_markup' => $telegram->buildInlineKeyboard($option2),
                    ];
                    $telegram->sendMessage($msg);


                    $this->sendNotifToAdmin($telegram,$account);
                }else{
                    $msg_text = 'شما پیش‌ از این حساب رایگان را دریافت کرده‌اید';
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => $msg_text,
                        'parse_mode' => 'HTML',
                    ];
                    $telegram->sendMessage($msg);
                    $option2 = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
                    $msg = [
                        'chat_id' => $chat_id,
                        'text' => 'جهت خرید مجدد، کلیک کنید',
                        'parse_mode' => 'HTML',
                        'reply_markup' => $telegram->buildInlineKeyboard($option2),
                    ];
                    $telegram->sendMessage($msg);

                }
            }

        }elseif($data == 'server_list'){
            $servers = Server::where('status','up')->get();
            foreach ($servers as $index => $server){
                $msg_text = "Server".($index+1).": ".$server->region;
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => $msg_text,
                    'parse_mode' => 'HTML',
                ];
                $telegram->sendMessage($msg);
            }

        /*
        * ========= REFUSE BTN CLICKED ===========
        */
        }elseif ($data == 'refused'){

           $this->refuseBtn();
            return 200;

        }
        elseif ($data == 'cisco'){
//            Cache::put($chat_id.'_service',['id'=>$chat_id,'value'=>'cisco'],1000);
            $this->cache->update(['service'=>'cisco']);
            $this->plans();
        }
        elseif ($data == 'openvpn'){
//            Cache::put($chat_id.'_service',['id'=>$chat_id,'value'=>'openvpn'],1000);
            $this->cache->update(['service'=>'openvpn']);
            $this->plans();
        }
        elseif ($data == 'open'){
//            Cache::put($chat_id.'_service',['id'=>$chat_id,'value'=>'open'],1000);
            $this->cache->update(['service'=>'open']);
            $this->plans();
        }
        elseif ($data == 'restart'){
            $this->restartBtn();
        }
        elseif ($data == 'y'){
//            DB::beginTransaction();
            $comment = Comment::where('user_id',$this->telegram->ChatID())->first();
            if(is_null($comment)){
                $comment = new Comment();
                $comment->username = $this->telegram->FirstName();
                $comment->user_id = $this->telegram->ChatID();
                $comment->vote = 1;
                $comment->save();
                $msg_text = 'باتشکر. نظر شما ثبت شد';
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => $msg_text,
                    'parse_mode' => 'HTML',
                ];
                $telegram->sendMessage($msg);
            }else{
                $comment->update(['vote'=> 1]);
                $msg_text = ' نظر شما به روز شد';
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => $msg_text,
                    'parse_mode' => 'HTML',
                ];
                $telegram->sendMessage($msg);
            }
//            DB::commit();

        }elseif ($data == 'n'){
//            DB::beginTransaction();
            $comment = Comment::where('user_id',$this->telegram->ChatID())->first();
            if(is_null($comment)){
                $comment = new Comment();
                $comment->username = $this->telegram->FirstName();
                $comment->user_id = $this->telegram->ChatID();
                $comment->vote = 0;
                $comment->save();
                $msg_text = 'باتشکر. نظر شما ثبت شد';
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => $msg_text,
                    'parse_mode' => 'HTML',
                ];
                $telegram->sendMessage($msg);
            }else{
                $comment->update(['vote'=> 0]);
                $msg_text = ' نظر شما به روز شد';
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => $msg_text,
                    'parse_mode' => 'HTML',
                ];
                $telegram->sendMessage($msg);
            }
//            DB::commit();

        }
        DB::commit();
//        else{
//            $msg_text = 'منظورت خرید حساب اقتصادی ۳ ماهه هستش ؟';
//            $msg = [
//                'chat_id' => $chat_id,
//                'text' => $msg_text,
//                'parse_mode' => 'HTML',
//            ];
//            $telegram->sendMessage($msg);
//        }
    }

    private function sendNotifToAdmin($telegram,$account){


        $msg = [
            'chat_id' => 83525910,
            'text' => ' خرید اکانت رایگان جدید '.$this->telegram->FirstName(),
            'parse_mode' => 'HTML',
        ];
        $telegram->sendMessage($msg);
    }

    private function sendFile(){
        $telegram = $this->telegram;
        $name = 'pezhman.ovpn';
        echo "
<body onload=\"submitform()\">
    <form  id=\"myForm\" action='https://api.telegram.org/bot844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8/sendDocument' method='post' enctype='multipart/form-data'>
        <input type='file' name='document' value='http://vitamin-g.ir/clients/$name'>
        <input type='text' name='chat_id' value='83525910'>
        <button id='send' type='submit'>Send</button>
    </form>
    
 <script type=\"text/javascript\" language=\"javascript\">
     function submitform(){
     document.getElementById('myForm').submit();
     document.getElementById(\"send\").click();
     }     
 </script>
 </body>
   ";
    }
    private function planRegistration($id,$telegram){
        $chat_id = $telegram->ChatID();
        $plan = Plan::where('id',$id)->first();
        $price = $plan->price;
        $time = $plan->month;
//        $service = Cache::get($chat_id.'_service')['value'];
        $service = $this->cache->service;
        $msg_text = "انتخاب شما حساب $time ماهه $service با قیمت $price تومان می‌باشد. لطفا جهت دریافت اطلاعات حساب، آدرس ایمیل و یا شماره موبایل خود را (به انگلیسی) وارد کنید.";
        $msg = [
            'chat_id' => $chat_id,
            'text' => $msg_text,
            'parse_mode' => 'HTML',
        ];
        $telegram->sendMessage($msg);
    }
    private function plans(){

        $chat_id = $this->telegram->ChatID();
        $telegram =  $this->telegram;
//        Cache::forget($chat_id);
        $service = $this->cache->service;
        $options = $this->planKeyboard();
        $msg = [
            'chat_id' => $chat_id,
            'text' => Emoji::blueCircle().' '.strtoupper($service).' '.Emoji::blueCircle() ,
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];
        $telegram->sendMessage($msg);
    }

    private function planKeyboard(){
        $telegram =  $this->telegram;
        $chat_id = $telegram->ChatID();
        $service = $this->cache->service;
        if($service == 'open'){
            $options = [
                array($telegram->buildInlineKeyBoardButton(Emoji::backhandIndexPointingLeft().' خرید حساب ۱ ماهه '.Emoji::backhandIndexPointingRight(),"",'1')),
                array($telegram->buildInlineKeyBoardButton(Emoji::backhandIndexPointingLeft().' خرید حساب ۳ ماهه اقتصادی '.Emoji::backhandIndexPointingRight(),'','3')),
                array($telegram->buildInlineKeyBoardButton(Emoji::globeShowingAmericas().' لیست سرورها '.Emoji::globeShowingAsiaAustralia(),'','server_list')),
                array($telegram->buildInlineKeyBoardButton(Emoji::downArrow().' آموزش اتصال و دانلود '.Emoji::downArrow(),'http://joyvpn.xyz'))

            ];
        }else{
            $options = [
                array($telegram->buildInlineKeyBoardButton(Emoji::backhandIndexPointingLeft().' خرید حساب ۱ ماهه '.Emoji::backhandIndexPointingRight(),"",'1')),
                array($telegram->buildInlineKeyBoardButton(Emoji::backhandIndexPointingLeft().' خرید حساب ۳ ماهه اقتصادی '.Emoji::backhandIndexPointingRight(),'','3')),
                array($telegram->buildInlineKeyBoardButton(Emoji::smilingFaceWithSunglasses().' دریافت حساب رایگان تست '.Emoji::smilingFaceWithSunglasses(),'','0')),
                array($telegram->buildInlineKeyBoardButton(Emoji::globeShowingAmericas().' لیست سرورها '.Emoji::globeShowingAsiaAustralia(),'','server_list')),
                array($telegram->buildInlineKeyBoardButton(Emoji::downArrow().' آموزش اتصال و دانلود '.Emoji::downArrow(),'http://joyvpn.xyz'))

            ];
        }

        return $options;

    }
    private function transactionList(){

        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
//        Cache::forget($chat_id);
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'این آیتم در حال راه‌اندازی می‌باشد',
            'parse_mode' => 'HTML',
        ];

        $telegram->sendMessage($msg);
    }

    private function refuseBtn(){
        DB::beginTransaction();
        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
        $options = [

            array($telegram->buildInlineKeyBoardButton('تماس با ما',"https://t.me/JoyVpn_Support"),$telegram->buildInlineKeyBoardButton('لیست تراکنش‌ها',"",'transactions')),
            array($telegram->buildInlineKeyBoardButton('شروع مجدد'))


        ];
//        Cache::forget($chat_id);
        $this->cache->update(['closed'=>1]);
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'درخواست شما لغو شد',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildKeyBoard($options),
        ];

        $telegram->sendMessage($msg);
        DB::commit();
    }

    private function contactUs(){

        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'https://t.me/JoyVpn_Support',
            'parse_mode' => 'HTML',
        ];

        $telegram->sendMessage($msg);

    }

}
