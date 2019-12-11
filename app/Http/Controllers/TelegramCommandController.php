<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Zarrin;
use Illuminate\Http\Request;
use App\Repo\TelegramErrorLogger;
use \Illuminate\Support\Facades\Cache;
use Telegram\Bot\Api;
class TelegramCommandController extends Controller
{
//    handles incoming messages from bot
    /**
     * @param Request $request
     */
    public $telegram;
    public function __construct()
    {
        $this->telegram = new \App\Repo\Telegram('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');
    }

    public function incoming(Request $request)
    {
        // $telegram = new Api('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');
        $dic = ['/start','refused','restart','لیست تراکنش‌ها','تماس با ما','شروع مجدد'];
        $data = '';
        $text = '';
        $telegram =  $this->telegram;
        $tgResp = $request->all();
        if(isset($tgResp['message'])){
            $userId = $tgResp['message']['from']['id'];
            $username = $tgResp['message']['from']['first_name'];
        }else{
            $userId = $tgResp['callback_query']['from']['id'];
            $username = $tgResp['callback_query']['from']['username'];
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

            return 200;

        }

        /*
       * ========= ReSTART BTN CLICKED ===========
       */
        if ($text == 'شروع مجدد'){

            $this->restartBtn();

            return 200;

        }


        /*
        * ========= Transaction List BTN CLICKED ===========
        */

        if($text == 'لیست تراکنش‌ها'){

            $this->transactionList();
            return 200;
        }

        /*
       * ========= Contact  BTN CLICKED ===========
       */

        if($text == 'تماس با ما'){

            $this->contactUs();
            return 200;
        }


        /*
         * ========= GLASSY BTN CLICKED ===========
         */
        if (isset($tgResp['callback_query'])) {
            $data = $tgResp['callback_query']['data'];
            $this->glassyBtn($data);
            return 200;
        }
//        if(strpos($text,'@') || (strlen($text) == 11 && is_numeric($text)) || Cache::get($chat_id) !== null){
            if(Cache::get($chat_id) !== null){
                if(!isset($tgResp['callback_query'])){

                    if(strpos($text,'@') || (strlen($text) == 11)){
                        $pass = 1;
                    }else{
//
                        $options = [

                            array($telegram->buildInlineKeyBoardButton('خرید حساب ۱ ماهه',"",'1')),
                            array($telegram->buildInlineKeyBoardButton('خرید حساب ۳ ماهه اقتصادی','','3')),
                            array($telegram->buildInlineKeyBoardButton('دریافت حساب رایگان تست','','0')),
                            array($telegram->buildInlineKeyBoardButton('لیست سرورها','','server_list')),
                            array($telegram->buildInlineKeyBoardButton('آموزش اتصال و دانلود','http://joyvpn.xyz'))

                        ];
                        $msg = [
                            'chat_id' => $chat_id,
                            'text' => 'ایمیل و یا شماره موبایل را نادرست وارد کردید',
                            'parse_mode' => 'HTML',
                        ];
                        $telegram->sendMessage($msg);
                    }


                    if(isset($pass)) {
                        $cached = Cache::get($chat_id);

                        Cache::forget($chat_id);

                        if ($cached['value'] == 1) {

                            if (strpos($text, '@')) {

                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => 10000, 'email' => $text, 'plan_id' => 1]);
                            } else {
                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => 10000, 'phone' => $text, 'plan_id' => 1]);
                            }
                            $result = $zarrin->create();
                            if($result != 404){
                                $option = [
                                    array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت', 'https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]), $telegram->buildInlineKeyboardButton('انصراف', '', 'refused'))
                                ];
                                $msg_text = 'مبلغ قابل پرداخت : ۱۰۰۰۰ تومان';
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
                            }

                        } elseif ($cached['value'] == 3) {

                            if (strpos($text, '@')) {

                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => 20000, 'email' => $text, 'plan_id' => 2]);
                            } else {
                                $zarrin = new Zarrin(['username' => $username, 'user_id' => $userId, 'amount' => 20000, 'phone' => $text, 'plan_id' => 2]);
                            }
                            $result = $zarrin->create();
                            $option = [
                                array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت', 'https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]), $telegram->buildInlineKeyboardButton('انصراف', '', 'refused'))
                            ];
                            $msg_text = 'مبلغ قابل پرداخت : ۲۰۰۰۰ تومان';
                            $msg = [
                                'chat_id' => $chat_id,
                                'text' => $msg_text,
                                'parse_mode' => 'HTML',
                                'reply_markup' => $telegram->buildInlineKeyboard($option)
                            ];
                            $telegram->sendMessage($msg);
                        }
                    }
                }


            }elseif(!in_array($text,$dic) || !in_array($data,$dic)){

                $options = [

                    array($telegram->buildInlineKeyBoardButton('خرید حساب ۱ ماهه',"",'1')),
                    array($telegram->buildInlineKeyBoardButton('خرید حساب ۳ ماهه اقتصادی','','3')),
                    array($telegram->buildInlineKeyBoardButton('دریافت حساب رایگان تست','','0')),
                    array($telegram->buildInlineKeyBoardButton('لیست سرورها','','server_list')),
                    array($telegram->buildInlineKeyBoardButton('آموزش اتصال و دانلود','http://joyvpn.xyz'))

                ];
                $msg = [
                    'chat_id' => $chat_id,
                    'text' => 'لطفا از موارد زیر انتخاب کنید',
                    'parse_mode' => 'HTML',
                    'reply_markup' => $telegram->buildInlineKeyboard($options)
                ];
                $telegram->sendMessage($msg);
            }


    }

    private function startBtn(){

        $chat_id = $this->telegram->ChatID();
        $telegram =  $this->telegram;
        Cache::forget($chat_id);

        $options = [

            array($telegram->buildInlineKeyBoardButton('خرید حساب ۱ ماهه',"",'1')),
            array($telegram->buildInlineKeyBoardButton('خرید حساب ۳ ماهه اقتصادی','','3')),
            array($telegram->buildInlineKeyBoardButton('دریافت حساب رایگان تست','','0')),
            array($telegram->buildInlineKeyBoardButton('لیست سرورها','','server_list')),
            array($telegram->buildInlineKeyBoardButton('آموزش اتصال و دانلود','http://joyvpn.xyz'))

        ];

        $msg = [
            'chat_id' => $chat_id,
            'text' => 'سلام. از حسن انتخاب شما کمال تشکر را داریم. برای خرید حساب روی طرح مورد نظر کلیک کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];

        $telegram->sendMessage($msg);
    }

    private function restartBtn(){

        $chat_id = $this->telegram->ChatID();
        $telegram =  $this->telegram;
        Cache::forget($chat_id);

        $options = [

            array($telegram->buildInlineKeyBoardButton('خرید حساب ۱ ماهه',"",'1')),
            array($telegram->buildInlineKeyBoardButton('خرید حساب ۳ ماهه اقتصادی','','3')),
            array($telegram->buildInlineKeyBoardButton('دریافت حساب رایگان تست','','0')),
            array($telegram->buildInlineKeyBoardButton('لیست سرورها','','server_list')),
            array($telegram->buildInlineKeyBoardButton('آموزش اتصال و دانلود','http://joyvpn.xyz'))

        ];

        $msg = [
            'chat_id' => $chat_id,
            'text' => 'جهت خرید روی طرح مورد‌نظر کلیک کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];

        $telegram->sendMessage($msg);
    }

    private function glassyBtn($data){

        $telegram =  $this->telegram;
        $chat_id = $this->telegram->ChatID();
        if($data == '1'){
            Cache::put("$chat_id",['id'=>$chat_id,'value'=>1],1000);
            $msg_text = 'انتخاب شما حساب ۱ ماهه با قیمت ۱۰۰۰۰ تومان می‌باشد. لطفا برای دریافت نام کاربری و کلمه عبور vpn، ایمیل و یا شماره موبایل  خود را ارسال کنید';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
        }elseif($data == '3'){
            Cache::put("$chat_id",['id'=>$chat_id,'value'=>3],1000);
            $msg_text = 'انتخاب شما حساب ۳ ماهه اقتصادی با قیمت ۲۰۰۰۰ تومان می‌باشد. لطفا برای دریافت نام کاربری و کلمه عبور vpn، ایمیل و یا شماره موبایل خود را ارسال کنید';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
        }elseif($data == '0'){
            Cache::put("$chat_id",['id'=>$chat_id,'value'=>0],1000);
            $msg_text = 'حساب تست ۳ روز اعتبار خواهد داشت';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
            $msg_text = ' username: fi844889 ';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
            $msg_text = 'password : 508273';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
        }elseif($data == 'server_list'){
            $msg_text = 'Server 1 : fi.joyvpn.xyz';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
            $msg_text = 'Server 2 : uk.joyvpn.xyz';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
        /*
        * ========= REFUSE BTN CLICKED ===========
        */
        }elseif ($data == 'refused'){

           $this->refuseBtn();
            return 200;

        }
        else{
            $msg_text = 'منظورت خرید حساب اقتصادی ۳ ماهه هستش ؟';
            $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
            ];
            $telegram->sendMessage($msg);
        }
    }

    private function transactionList(){

        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
        Cache::forget($chat_id);


        $msg = [
            'chat_id' => $chat_id,
            'text' => 'این آیتم در حال راه‌اندازی می‌باشد',
            'parse_mode' => 'HTML',
        ];

        $telegram->sendMessage($msg);
    }

    private function refuseBtn(){
        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
        $options = [

            array($telegram->buildInlineKeyBoardButton('تماس با ما',"https://t.me/Sahand_MG"),$telegram->buildInlineKeyBoardButton('لیست تراکنش‌ها',"",'transactions')),
            array($telegram->buildInlineKeyBoardButton('شروع مجدد'))


        ];
        Cache::forget($chat_id);
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'درخواست شما لغو شد',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildKeyBoard($options),
        ];

        $telegram->sendMessage($msg);
    }

    private function contactUs(){

        $telegram = $this->telegram;
        $chat_id = $this->telegram->ChatID();
        $msg = [
            'chat_id' => $chat_id,
            'text' => 'https://t.me/Sahand_MG',
            'parse_mode' => 'HTML',
        ];

        $telegram->sendMessage($msg);

    }

}
