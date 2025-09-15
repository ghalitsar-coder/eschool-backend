<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugMultipartData
{
    public function handle(Request $request, Closure $next)
    {
        // Only debug for attendance update requests
        if ($request->isMethod('PUT') && str_contains($request->path(), 'attendance/records/')) {
            \Log::info('Debug Multipart Data Middleware:', [
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'method' => $request->method(),
                'path' => $request->path(),
                'all_data' => $request->all(),
                'post_data' => $request->post(),
                'files' => $request->allFiles(),
                'raw_content' => substr($request->getContent(), 0, 500), // First 500 chars only
                'has_is_present' => $request->has('is_present'),
                'is_present_value' => $request->input('is_present'),
                'is_present_type' => gettype($request->input('is_present')),
                'server_vars' => [
                    'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
                    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'not_set',
                    'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'not_set',
                ]
            ]);
        }
        
        return $next($request);
    }
}

