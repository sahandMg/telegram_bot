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
    public function incoming(Request $request)
    {
        // $telegram = new Api('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');

        $telegram = new \App\Repo\Telegram('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');

        $tgResp = $request->all();
        $userId = $telegram->UserID();
        $username = $telegram->Username();
        // $isBot = $tgResp['message']['from']['is_bot'];
        $text = $telegram->Text();
        $chat_id = $telegram->ChatID();

        Cache::put("tg",$tgResp,10000);
        if ($text == '/start'){
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
        if (isset($tgResp['callback_query'])) {
            $data = $tgResp['callback_query']['data'];

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
            }elseif ($data == 'refused'){

                $options = [

                    array($telegram->buildInlineKeyBoardButton('تماس با ما',"",'contact'),$telegram->buildInlineKeyBoardButton('شروع مجدد',"",'/start'))


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
        if(strpos($text,'@') || (strlen($text) == 11 && is_numeric($text))){
            if(Cache::get($chat_id) !== null){
                $cached = Cache::get($chat_id);

                Cache::forget($chat_id);

                if($cached['value'] == 1){

                    if(strpos($text,'@')){

                        $zarrin = new Zarrin(['username'=>$username,'user_id'=>$userId,'amount'=> 10000,'email'=>$text,'plan_id'=>1]);
                    }else{
                        $zarrin = new Zarrin(['username'=>$username,'user_id'=>$userId,'amount'=> 10000,'phone'=>$text,'plan_id'=>1]);
                    }
                    $result = $zarrin->create();
                    $option = [
                        array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت','https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]),$telegram->buildInlineKeyboardButton('انصراف','','refused'))
                    ];
                    $msg_text = 'مبلغ قابل پرداخت : ۱۰۰۰۰ تومان';
                }elseif ($cached['value'] == 3){

                    if(strpos($text,'@')){

                        $zarrin = new Zarrin(['username'=>$username,'user_id'=>$userId,'amount'=> 20000,'email'=>$text,'plan_id'=>2]);
                    }else{
                        $zarrin = new Zarrin(['username'=>$username,'user_id'=>$userId,'amount'=> 20000,'phone'=>$text,'plan_id'=>2]);
                    }
                    $result = $zarrin->create();
                    $option = [
                        array($telegram->buildInlineKeyboardButton('هدایت به درگاه پرداخت','https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]),$telegram->buildInlineKeyboardButton('انصراف','','refused'))
                    ];
                    $msg_text = 'مبلغ قابل پرداخت : ۲۰۰۰۰ تومان';
                }
                $msg = [
                'chat_id' => $chat_id,
                'text' => $msg_text,
                'parse_mode' => 'HTML',
                'reply_markup' => $telegram->buildInlineKeyboard($option)
                ];
                $telegram->sendMessage($msg);
            }
//            else{
//                $msg = [
//                    'chat_id' => $chat_id,
//                    'text' => 'ایمیل و یا شماره موبایل را اشتباه ارسال کرده‌اید',
//                    'parse_mode' => 'HTML',
//                ];
//                $telegram->sendMessage($msg);
//            }
        }
        if($text == 'شروع مجدد'){
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
                'text' => 'JOY VPN',
                'parse_mode' => 'HTML',
                'reply_markup' => $telegram->buildInlineKeyboard($options),
            ];

            $telegram->sendMessage($msg);

        }

    }
}
