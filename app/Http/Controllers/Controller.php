<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function buildResponse($message, $data)
    {
        return response([
            'code' => 200,
            'msg' => $message,
            'data' => $data,
        ]);
    }

    public function notfoundResponse($message)
    {
        return response([
            'code' => 400,
            'error' => $message,
        ]);
    }

    public function errorResponse($message)
    {
        return response([
            'code' => 500,
            'error' => $message,
        ]);
    }
}

