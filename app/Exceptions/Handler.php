<?php

namespace App\Exceptions;

use App\Jobs\TelegramNotification;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {

        $chat_id = 83525910;
        $msg = [
            'chat_id' => $chat_id,
            'text' =>
                $exception->getMessage() == null ?
                'No Error --> '. $exception->getFile().' Code = '.$exception->getCode():
                $exception->getMessage().' In --> '.$exception->getFile(),
            'parse_mode' => 'HTML',
        ];
       TelegramNotification::dispatch($msg);

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
