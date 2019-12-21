<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send';

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
        $mails = [
            's24.moghadam@gmail.com','s24.moghadam@yahoo.com',
            's25.moghadam@yahoo.com','s26.moghadam@yahoo.com',
            's27.moghadam@yahoo.com','s28.moghadam@yahoo.com',
            's25.moghadam@gmail.com','s26.moghadam@gmail.com',
            's27.moghadam@gmail.com','s28.moghadam@gmail.com',
            's29.moghadam@gmail.com','s30.moghadam@gmail.com',
            's31.moghadam@gmail.com','s32.moghadam@gmail.com',
            's33.moghadam@gmail.com','newton.greens.ng@gmail.com'
        ];
//    $mails = ['s23.moghadsadsadasad@gmail.com'];
        for($i=0;$i<50;$i++){
//    return view('welcome');
            Mail::send('welcome',[],function($message)use($mails,$i){
                $message->to($mails[rand(0,15)]);
//            $message->from('support@joyvpn.xyz');
                $message->subject('!!دنیای بدون مرز!!');
            });
            sleep(0.5);
        }
    }
}
