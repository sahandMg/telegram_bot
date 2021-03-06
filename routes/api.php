<?php

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use \Illuminate\Support\Facades\Cache;
use \Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('check', function() {

    // $telegram = new Api(env('BOT_TOKEN'));
    // $updates = $telegram->getMe();

    $telegram = new \App\Repo\Telegram('844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8');
//    $telegram = new Api(env('BOT_TOKEN'));
    dd($telegram->getWebhookUpdates());
    // $chat_id = $telegram->ChatID();
    // $content = array('chat_id' => $chat_id, 'text' => 'Test');
    // $telegram->sendMessage($content);
});
Route::post('tg/update','TelegramCommandController@incoming');


Route::get('key',function(){



//     DB::commit();

    DB::transaction(function () {

        $user = new \App\User();
        $user->name = 'shandddsd';
        $user->email = 's@dsas';
        $user->password = 'dsadas';
        $user->save();
    }, 5);
});

Route::get('get',function(){


    $user = new \App\User();
    dd($user->where('name','shandddd')->first());
});

Route::post('hook',function (Request $request){
    if($request->type == 'success'){
        $body = $request->all();
        $msg = $body[0];
        $msg2 = $body[1];
        $msg3 = $body[2];
        $msg4 = $body[3];
        $msg5 = $body[4];
        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $telegram->sendMessage($msg);
        $telegram->sendMessage($msg2);
        $telegram->sendMessage($msg3);
        $telegram->sendMessage($msg4);
        $telegram->sendMessage($msg5);
        $options = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
        $msg6 = [
            'chat_id' => $msg['chat_id'],
            'text' => 'جهت خرید مجدد، کلیک کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];
        $telegram->sendMessage($msg6);

    }elseif($request->type == 'canceled'){
        $body = $request->all();
        $msg = $body[0];
        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $telegram->sendMessage($msg);
        $options = [array($telegram->buildInlineKeyBoardButton('شروع مجدد','','restart'))];
        $msg2 = [
            'chat_id' => $msg['chat_id'],
            'text' => 'جهت خرید مجدد، کلیک کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $telegram->buildInlineKeyboard($options),
        ];
        $telegram->sendMessage($msg2);
    }
    elseif($request->type == 'warning'){
        $body = $request->all();
        $msg = $body[0];
        $telegram = new \App\Repo\Telegram(env('BOT_TOKEN'));
        $telegram->sendMessage($msg);
}

});