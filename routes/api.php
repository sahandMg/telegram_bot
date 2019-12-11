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

    dd(__DIR__);
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
        $user->name = 'shandddd';
        $user->email = 's@dsas';
        $user->password = 'dsadas';
        $user->save();
    }, 5);
});

Route::get('get',function(){


    $user = new \App\User();
    dd($user->where('name','shandddd')->first());
});