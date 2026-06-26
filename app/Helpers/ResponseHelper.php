<?php

if (!function_exists('api_response')) {
    /**
     * Standard API JSON response.
     *
     * @param mixed $data        Data payload (optional)
     * @param string|null $message Message string (optional)
     * @param int $status        HTTP status code (default 200)
     * @param array $meta        Additional meta information (optional)
     * @return \Illuminate\Http\JsonResponse
     */
    function api_response($data = null, $message = null, $status = 200, array $meta = [])
    {
        $payload = [
            'success' => $status >= 200 && $status < 300,
            'data'    => $data,
            'message' => $message,
        ];
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }
        return response()->json($payload, $status);
    }
}
