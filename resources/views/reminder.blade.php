<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <title>Reminder</title>
</head>
<style>
    html, body {
        background-color: #fff;
        color: #636b6f;
        font-family: 'Nunito', sans-serif;
        font-weight: 200;
        height: 100vh;
        margin: 0;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
    .container{
        margin: 0 auto;
    }
    .img{
        margin:10px 0;
    }
    .support{
        background: #2090ff;padding:15px;font-family: "Arial";font-size: 12px;margin: 20px auto;border: 1px dashed black;border-radius: 10px;width: 20%;
        font-size:18px;color:white;
    }
    .tamdid{
        background: #27a328;padding:15px;font-family: "Arial";font-size: 12px;margin: 20px auto;border: 1px dashed black;border-radius: 10px;width: 20%;
        font-size:18px;color:white;
    }
    </style>
<body>

<div id="editbody1" class="container">
    <div style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif;">
        <div id="v1editbody1">
            <div style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif;">
                <div id="v1v1editbody1">
                    <div style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif;">
                        <div id="v1v1v1editbody1">
                            <div style="font-size: 10pt; font-family: Verdana,Geneva,sans-serif;">
                                <p style="text-align: right;">
                                    <img class="img" style="display: block; margin-left: auto; margin-right: auto;" src="http://joyvpn.xyz/files/joyvpn.png" width="300" height="93" /></p>
                                </p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">با سلام،</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">کاربرگرامی، از آنجا که سر رسید اعتبار حساب شما {{$account->expires_at}} می&zwnj;باشد، به اطلاع می&zwnj;رساند جهت جلوگیری از قطع سرویس خریداری شده، اقدام به تمدید آن نمایید.</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">اطلاعات حساب :&nbsp;</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">حساب {{$plan->month}} ماهه {{$trans->service}} به قیمت {{$plan->price}} تومان.</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">نام کاربری : {{$account->username}}</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">کلمه عبور: {{$account->password}}</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">تاریخ خرید - انقضا : {{\Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($trans->updated_at))->format('%d %B %Y')}} - {{$account->expires_at}}</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;"><span style="font-size: 11pt;"><strong><span style="font-family: Arial, palatino, serif;">جهت تمدید حساب، روی دکمه زیر کلیک کنید.</span></strong></span></p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;">&nbsp;</p>
                                <p dir="rtl" style="text-align: right; padding-right: 30px;">&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="text-align: center">
    <a href="{{route('tamdid')}}?usr={{$account->username}}&id={{$account->user_id}}&trans_id={{$trans->trans_id}}" class="btn btn-primary btn-link tamdid">تمدید حساب</a>
</div>
<div style="text-align: center">
    <a href="https://t.me/JoyVpn_Support" class="btn btn-primary btn-link support">پشتیبانی</a>
</div>
</body>
</html>