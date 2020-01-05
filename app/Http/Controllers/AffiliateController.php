<?php

namespace App\Http\Controllers;

use App\Accounts;
use App\Affiliate;
use App\Repo\IpFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
//    sends affiliate notifications to users

    public function sendNotif(){

        $text = '<b>📣📣 خبر خبر </b>‼️
💥<b>⚡️دوستاتو بیار به joy vpn ، حساب رایگان ببر!🤩🤩</b>

✅ اگه ۵ تا از دوستات از لینک زیر vpn بخرن یه حساب ۱ ماهه نوش جانت😋 اگه برسونیش به ۱۰ تا ، حسابت به ۳ ماهه ارتقا پیدا میکنه!! به همین راحتی😉😎

http://joyvpn.xyz/missyou/8726653

دکمه زیر رو بزن تا تعداد دفعاتی که از طریق لینکت، خرید انجام شده ببینی. کافیه حداقل برسونیش به ۵ 🤓';
        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $options = [
            array($telegram->buildInlineKeyboardButton('تعداد خرید ها','','shareCounter'))
        ];
        $userIds = Accounts::where('used',1)->get()->pluck('user_id')->toArray();
        $userIds = array_values(array_unique($userIds));
//        for($i=0;$i<count($userIds);$i++){

            $msg = [
                'chat_id' => 83525910,
                'text' => $text,
                'parse_mode'=>'HTML',
                'reply_markup' => $telegram->buildInlineKeyBoard($options)
            ];
            \App\Jobs\TelegramNotification::dispatch($msg);
//        }

    }
//    users landing
    public function landing(Request $request){

        $user_id = $request->id;
        $sender = Accounts::where('user_id',$user_id)->first();
        if(is_null($sender)){
            return 'لینک نامعتبر است';
        }else{
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
