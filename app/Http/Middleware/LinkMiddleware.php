<?php

namespace App\Http\Middleware;

use App\ShortLink;
use Closure;

class LinkMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $uri = $_SERVER['REQUEST_URI'];
//        $linkAddress = ShortLink::where('abbr',$uri)->first();
//        if(is_null($linkAddress)){
//            return $next($request);
//        }else{
//            return redirect('pay.joyvpn.xyz/'.$linkAddress->origin);
//        }

        return $next($request);

    }
}
