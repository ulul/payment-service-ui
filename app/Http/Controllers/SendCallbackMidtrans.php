<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SendCallbackMidtrans extends Controller
{
    /**
     * Send succcess request to callback backend 
     * @param Request $request
     * @return JsonResponse
     */
    public function success(Request $request): JsonResponse
    {
        $token = $request->session()->get('api_token');

         try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') . '/mock-callback/success', [
                    'order_id' => $request->order_id,
                ]);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Purchase service unreachable.'], 503);
        }

         if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        if (in_array($response->status(), [401, 403], true)) {
            $request->session()->forget('api_token');
            return response()->json(['message' => 'Session expired'], 401);
        }

        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Purchase failed.') : 'Purchase failed.';
        
        return response()->json(['message' => $message], $response->status());
    }

    /**
     * Send failed request to callback backend 
     * @param Request $request
     * @return JsonResponse
     */
    public function failed(Request $request):JsonResponse
    {
        $token = $request->session()->get('api_token');

         try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') . '/mock-callback/failed', [
                    'order_id' => $request->order_id,
                ]);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Purchase service unreachable.'], 503);
        }

         if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        if (in_array($response->status(), [401, 403], true)) {
            $request->session()->forget('api_token');
            return response()->json(['message' => 'Session expired'], 401);
        }

        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Purchase failed.') : 'Purchase failed.';
        
        return response()->json(['message' => $message], $response->status());

    }
}
