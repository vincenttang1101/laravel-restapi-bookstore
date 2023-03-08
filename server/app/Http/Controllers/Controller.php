<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function resSuccess($data, $content)
    {
        if (!empty($data)) {
            return response()->json([
                'success' => true,
                'status' => $content[0],
                'message' => $content[1],
                'data' => $data,
            ], $content[0]);
        } else {
            return response()->json([
                'success' => true,
                'status' => $content[0],
                'message' => $content[1],
            ], $content[0]);
        }
    }

    protected function resError($content)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'status' => $content[0],
                'message' => $content[1],
            ],
        ], $content[0]);
    }

    protected function resValidator($content)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'status' => $content[0],
                'fields' => $content[1],
                'message' => 'Something is wrong with this field',
            ]
        ], $content[0]);
    }
}
