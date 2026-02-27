<?php

namespace Packages\EzKnowledgeBase\Helpers;

use Illuminate\Http\JsonResponse;

class ApiErrorResponse
{
    /**
     * Build a standardized API error response.
     *
     * @param string $message Human-readable error message
     * @param string $code Machine-readable error code
     * @param int $status HTTP status code
     * @param array|null $details Additional error details (e.g., validation errors)
     */
    public static function make(string $message, string $code, int $status, ?array $details = null): JsonResponse
    {
        $error = [
            'message' => $message,
            'code' => $code,
        ];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return response()->json(['error' => $error], $status);
    }
}
