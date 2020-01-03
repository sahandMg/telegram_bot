<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MailController extends Controller
{
    public function get_sendMail(){

        return view('adv');
    }
    public function sendMail(Request $request){

        $name = $request->file('accounts')->getClientOriginalName();
        $request->file('accounts')->move(public_path('files'), $name);
        Excel::import(new UsersImport(), public_path('files/' . $name));
        unlink(public_path('files/'.$name));
        return '200';
    }
}
