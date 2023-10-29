<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiBaseController extends Controller
{
    protected function errorResponse($message, $data = null, $code = Response::HTTP_BAD_REQUEST)
    {
        $response = [
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ];

        return response()->json($response, $code);
    }

    protected function successResponse($message, $data = null, $code = Response::HTTP_OK)
    {
        $response = [
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ];

        return response()->json($response, $code);
    }
}
