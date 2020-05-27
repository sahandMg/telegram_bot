<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Emoji\Emoji;

class checkPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email To Test Addresses';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $resp = Cache::get('resp');
        $changes = [];
        for($t=0;$t<count($resp);$t++){
            for($s=0;$s<count($resp[$t]);$s++){
                $query = DB::table('ps4_game')->where('title',$resp[$t][$s][0])->first();
                if(!is_null($query)){
                    if($query->price != $resp[$t][$s][1]){
                        $temp = [
                            'title'=>$resp[$t][$s][0],
                            'new_price'=>$resp[$t][$s][1],
                            'old_price'=>$query->price,
                            'avatar'=>$query->avatar,
                            'link'=>$query->link
                        ];
                        DB::table('ps4_game')->where('title',$resp[$t][$s][0])->update([
                            'price'=>$resp[$t][$s][1],
                            'updated_at'=>Carbon::now()
                        ]);
                        array_push($changes,$temp);
                    }
                }
            }
        }
        $data = ['changes'=>$changes];
        if(count($changes) > 0){
            Mail::send('ps4_email',$data,function($msg){
                $msg->to('s23.moghadam@gmail.com');
                $msg->from('playstation@joyvpn.xyz');
                $msg->subject('PlayStation Store Prices');
            });
        }

//        return view('ps4_email', compact('changes'));
    }
}
