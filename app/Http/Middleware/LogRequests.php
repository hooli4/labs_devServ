<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id() ?? null;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $method = $request->method();
        $fullUrl = $request->fullUrl();
        $path = $request->route()->getAction('controller');
        $requestBody = $request->getContent();
        $requestHeaders = json_encode($request->headers->all());

        $response = $next($request);
        
        $responseStatus = $response->status();
        $responseBody = $response->getContent();
        $responseHeaders = json_encode($response->headers->all());

        LogRequest::create([
            'URL_API_METHOD' => $fullUrl,
            'METHOD_HTTP_REQUEST' => $method,
            'CONTROLLER_PATH' => $path,
            'NAME_METHOD_CONTROLLER' => class_basename($path),
            'BODY_REQUEST' => $requestBody,
            'HEADERS_REQUEST' => $requestHeaders,
            'USER_ID' => $userId,
            'USER_IP_ADDRESS' => $ipAddress,
            'USER_USER-AGENT' => $userAgent,
            'CODE_STATUS_RESPONSE' => $responseStatus,
            'BODY_RESPONSE' => $responseBody,
            'HEADERS_RESPONSE' => $responseHeaders,
            'TIME_CALL' => Carbon::now(),
        ]);

        return $response;
    }
}
