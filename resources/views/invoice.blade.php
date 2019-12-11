<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css' />

    <title>Invoice</title>
    <style>
        /*	Reset & General
---------------------------------------------------------------------- */
        * { margin: 0px; padding: 0px; }
        body {
            background: #ecf1f5;
            font:14px "Open Sans", sans-serif;
            text-align:center;
        }

        .tile{
            width: 100%;
            background:#fff;
            border-radius:5px;
            box-shadow:0px 2px 3px -1px rgba(151, 171, 187, 0.7);
            float:left;
            transform-style: preserve-3d;
            margin: 10px 5px;

        }

        .header{
            border-bottom:1px solid #ebeff2;
            padding:19px 0;
            text-align:center;
            color:#59687f;
            font-size:600;
            font-size:19px;
            position:relative;
        }

        .banner-img {
            padding: 5px 5px 0;
        }

        .banner-img img {
            width: 100%;
            border-radius: 5px;
        }

        .dates{
            border:1px solid #ebeff2;
            border-radius:5px;
            padding:20px 0px;
            margin:10px 20px;
            font-size:16px;
            color:#5aadef;
            font-weight:600;
            overflow:auto;
        }
        .dates div{
            float:left;
            width:50%;
            text-align:center;
            position:relative;
        }
        .dates strong,
        .stats strong{
            display:block;
            color: #5c646d;
            font-size:15px;
            font-weight:700;
        }
        .dates span{
            width:1px;
            height:40px;
            position:absolute;
            right:0;
            top:0;
            background:#ebeff2;
        }
        .stats{
            border-top:1px solid #ebeff2;
            background:#f7f8fa;
            overflow:auto;
            padding:15px 0;
            font-size:16px;
            color:#59687f;
            font-weight:600;
            border-radius: 0 0 5px 5px;
        }
        .stats div{
            border-right:1px solid #ebeff2;
            width: 33.33333%;
            float:left;
            text-align:center
        }

        .stats div:nth-of-type(3){border:none;}

        div.footer {
            text-align: right;
            position: relative;
            margin: 20px 5px;
        }

        div.footer a.Cbtn{
            padding: 10px 25px;
            background-color: #DADADA;
            color: #666;
            margin: 10px 2px;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: none;
            border-radius: 3px;
        }

        div.footer a.Cbtn-primary{
            background-color: #5AADF2;
            color: #FFF;
        }

        div.footer a.Cbtn-primary:hover{
            background-color: #7dbef5;
        }

        div.footer a.Cbtn-danger{
            background-color: #fc5a5a;
            color: #FFF;
        }

        div.footer a.Cbtn-danger:hover{
            background-color: #fd7676;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">


        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"></div>

        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <div class="tile">
                <div class="wrapper">
                    <div class="header">رسید مشتری</div>

                    <div class="banner-img">
                        <img src="{{URL::asset('img/joyvpn.jpg')}}" alt="joyVpn LOGO">
                    </div>

                    <div class="dates">
                        <div class="start">
                            <strong>فعالسازی</strong>{{\Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::now())->format('%B %d، %Y')}}
                            <span></span>
                        </div>
                        <div class="ends">
                            {{--<strong>انقضا</strong> {{\Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::now()->addMonths($plan->month))}}--}}
                            <strong>انقضا</strong> {{\Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::now()->addMonths($plan->month)->format('%B %d، %Y'))}}
                        </div>
                    </div>

                    <div class="stats">

                        <div>
                            <strong>مبلغ پرداختی</strong> {{$trans->amount}}
                        </div>

                        <div>
                            <strong>نام‌کاربری</strong> {{$account->username}}
                        </div>

                        <div>
                            <strong>کلمه عبور</strong> {{$account->password}}
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"></div>

        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"></div>

    </div>
</div>

</body>
</html>