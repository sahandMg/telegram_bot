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

    $servers = Server::where('status', 'up')->get();
    foreach ($servers as $server) {
        $ch = curl_init($server->ip . ':9095?username=aliii'.'&password=123123');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Telegram Bot');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
    }

});