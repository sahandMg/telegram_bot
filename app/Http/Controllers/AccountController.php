<?php

namespace App\Http\Controllers;

use App\Imports\AccountsImport;
use App\Server;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AccountController extends Controller
{

    public function index(){

        return view('upload_account');
    }

    public function post_index(Request $request)
    {

        $name = $request->file('accounts')->getClientOriginalName() . 'xlsx';
        $request->file('accounts')->move(public_path('files'), $name);
        Excel::import(new AccountsImport(), public_path('files/' . $name));
        unlink(public_path('files/'.$name));
        return '200';
    }
}
