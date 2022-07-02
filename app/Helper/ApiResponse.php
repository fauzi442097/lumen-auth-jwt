<?php

namespace App\Helper;

class ApiResponse
{

    public static function success($message, $data = null)
    {
        $responseBody['code'] = 200;
        $responseBody['message'] = $message;

        if (!is_null($data)) {
            $responseBody['data'] = $data;
        }

        return response()->json($responseBody, 200);
    }

    /**
     * @OA\Schema(
     *     schema="NotFoundResponse",
     *     type="object",
     *     title="Response Not Found",
     *     @OA\Property(property="code", type="number", example="404"),
     *     @OA\Property(property="message", type="string", example="Data Not Found")
     * )
     */
    public static function notFound($message)
    {
        $responseBody['code'] = 404;
        $responseBody['message'] = $message;

        return response()->json($responseBody, 404);
    }

    public static function internalServerError($message)
    {
        $responseBody['code'] = 500;
        $responseBody['message'] = 'Internal Server Error';
        $responseBody['error'] = $message;

        return response()->json($responseBody, 500);
    }

    public static function badRequest($message, $errors = null)
    {
        $responseBody['code'] = 400;
        $responseBody['message'] = $message;

        if (!is_null($errors)) {
            $responseBody['errors'] = $errors;
        }

        return response()->json($responseBody, 400);
    }

    public static function unauthorized($message = 'Unauthorized')
    {
        return response()->json([
            'code' => 401,
            'message' => $message
        ], 401);
    }
}
