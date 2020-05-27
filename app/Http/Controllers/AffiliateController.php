<?php

namespace App\Http\Controllers;

use App\Accounts;
use App\Affiliate;
use App\CacheData;
use App\Repo\IpFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
//    sends affiliate notifications to users

    public function sendNotif(){

        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $options = [
            array($telegram->buildInlineKeyboardButton('تعداد خرید ها','','shareCounter'))
        ];
        $userIds = Accounts::where('used',1)->get()->pluck('user_id')->toArray();
        $userIds = array_values(array_unique($userIds));
        $id = $_GET['id'];
//        for($i=0;$i<count($userIds);$i++){
//        $text1 = "<b>📣📣 خبر خبر </b>‼️
//💥 <b>⚡️دوستاتو بیار به joy vpn ، حساب رایگان ببر!🤩🤩</b>
//
//✅ اگه 3 تا از دوستات از لینک زیر vpn بخرن یه حساب ۱ ماهه رایگان نوش جانت😋 اگه برسونیش به ۶ تا ، حسابت به ۳ ماهه ارتقا پیدا میکنه!! به همین راحتی😉😎
//کافیه لینک زیر رو برای دوستات بفرستی👇👇
//
//<a href='http://pay.joyvpn.xyz/af/$id'>http://joyvpn.xyz/af/$id</a>
//
//✅دکمه زیر رو بزن تا تعداد دفعاتی که از طریق لینکت، خرید انجام شده ببینی. کافیه حداقل برسونیش به ۵ 🤓";
//
//
        $text2 = "
        
      <b>  🛑🛑 دوست نداری بابت VPN پول خرج کنی؟ 🤔</b>

✳️✳️مشکلی نیست! ما بهت رایگانشو میدیم😊🤩
✅ اگه ۳ تا از دوستات از لینک زیر vpn بخرن، یک حساب ۱ ماهه رایگان تقدیمت میشه😋 ✅ 
اگه برسونیش به ۶ نفر، حسابت به ۳ ماهه ارتقا پیدا میکنه!! به همین راحتی😉😎
 ✳️ کافیه لینک زیر رو برای دوستات بفرستی👇👇

<a href='http://pay.joyvpn.xyz/af/$id'>http://joyvpn.xyz/af/$id</a>

❇️ دکمه زیر رو بزن تا تعداد دفعاتی که از طریق لینکت، خرید انجام شده ببینی.
🔥 کافیه حداقل برسونیش به ۳🔥
        
        ";

            $msg = [
                'chat_id' => $id,
                'text' => $text2,
                'parse_mode'=>'HTML',
                'reply_markup' => $telegram->buildInlineKeyBoard($options)
            ];
//            \App\Jobs\TelegramNotification::dispatch($msg);
        $telegram->sendMessage($msg);
        \App\Jobs\Activities::dispatch($id,'پیام افیلیت');
//        }

    }
//    users landing
    public function landing(Request $request){

        $user_id = $request->id;
        $sender = CacheData::where('user_id',$user_id)->first();
        $sender2 = Accounts::where('user_id',$user_id)->first();
         if(is_null($sender) && is_null($sender2)){
            return 'لینک نامعتبر است';
        }
        else{
            DB::beginTransaction();
            $ip = IpFinder::find();
            $affiliate = new Affiliate();
            $affiliate->inviter = $user_id;
            $affiliate->invitee = $ip;
            $affiliate->save();
            DB::commit();
            return redirect('https://t.me/JoyVpn_bot');
        }
    }

}
