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

Route::get('/',function (\Illuminate\Http\Request $request){

   echo '
    <form action="https://api.telegram.org/bot844102898:AAFMoS3d6BVX1CNA-TN7gnsegcBLqTCJqd8/sendDocument" method="post" enctype="multipart/form-data">
        <input type="file" name="document">
        <button type="submit">Send</button>
    </form>
   ';

});
Route::post('getfile',function (\Illuminate\Http\Request $request){

    dd($request->all());

})->name('getfile');

Route::get('run',function (){

    echo shell_exec("./com.sh sahand");
});
Route::get('test',function (){


});