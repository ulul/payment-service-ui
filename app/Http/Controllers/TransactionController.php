<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\PurchaseRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Get list of transactions
     */
    public function index(Request $request)
    {
        $token = $request->session()->get('api_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->get(rtrim(config('app.api_url'), '/') . '/api/transactions');
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Transaction service unreachable.'], 503);
        }

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        }

        if (in_array($response->status(), [401, 403], true)) {
            $request->session()->forget('api_token');
            return response()->json(['message' => 'Session expired'], 401);
        }

        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Failed to load transactions.') : 'Failed to load transactions.';
        
        return response()->json(['message' => $message], $response->status());
    }

    /**
     * Create a new transaction (purchase)
     */
    public function purchase(PurchaseRequest $request)
    {
        $token = $request->session()->get('api_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') . '/api/purchase', [
                    'amount' => (float) $request->amount,
                    'description' => $request->description,
                ]);
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Purchase service unreachable.'], 503);
        }

        if ($response->successful()) {
            return response()->json($response->json(), 201);
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
