<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 3);

        $logData = [
            'method' => $request->getMethod(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'duration' => $duration . 's',
            'request' => $this->sanitizeRequest($request->all()),
            'response_status' => $response->getStatusCode(),
            'response_body' => $this->getResponseData($response),
        ];

        Log::channel('api')->info('API_LOG: ' . $request->getPathInfo(), $logData);

        return $response;
    }

    /**
     * Sanitize sensitive data from request logs.
     */
    private function sanitizeRequest(array $data)
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'accessToken'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '********';
            }
        }
        return $data;
    }

    /**
     * Safely get response data.
     */
    private function getResponseData($response)
    {
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Optionally sanitize response if it contains sensitive data
            return $data;
        }

        return $content;
    }
}
