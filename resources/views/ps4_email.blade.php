<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mail</title>
</head>
<style>
    .container{
        width:90%
    }
    .card-box{
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        width: 100%;

    }
    .card img{
        width: 100%;
    }
    .card{
        box-sizing: border-box;
        width:50%;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        font-family: Roboto;
        border: 1px solid grey;
        border-radius: 10px;
        box-shadow: 1px 1px 1px gray;
        padding: 5px;
        margin: 10px;

    }
    h1,p{
        text-align: center;
        font-weight: bold;
        font-family: Roboto;
    }
    a{
        text-decoration: none;
        cursor: pointer;
        color: black;
    }
    @media screen and(max-width: 768px){

        .card-box{
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            width: 100%;

        }
    }
</style>
<body>

<div class="container">
    <h1>{{\Carbon\Carbon::now()}}</h1>
    <p>PS Store Prices</p>
    <div class="card-box">
        @for($i=0;$i<count($changes);$i++)
            <div class="card">
                <a href="{{$changes[$i]['link']}}">
                    <figure>
                        <img src="{{$changes[$i]['avatar']}}" alt="">
                    </figure>
                    <p>{{$changes[$i]['title']}}</p>
                    <p>New Price: {{$changes[$i]['new_price']}}</p>
                    <p>Old Price: {{$changes[$i]['old_price']}}</p>
                </a>
            </div>
        @endfor
    </div>
</div>

</body>
</html>