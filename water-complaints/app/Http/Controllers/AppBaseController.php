<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Flash;
use Response;

class AppBaseController extends BaseController
{
    protected function sendResponse($result, $message)
    {
        return response()->json(Response::makeResponse($message, $result));
    }

    protected function sendError($error, $code = 404)
    {
        return response()->json(Response::makeError($error, $code));
    }

    protected function sendSuccess($message)
    {
        return response()->json(Response::makeResponse($message));
    }
}
