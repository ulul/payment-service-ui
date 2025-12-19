<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\TopupRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TopupController extends Controller
{
    public function topup(TopupRequest $request)
    {

        $token = $request->session()->get('api_token');

        if (!$token) {
            return redirect()->route('login')->withErrors(['api' => 'Please login to continue.']);
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') . '/api/topup', [
                    'amount' => (float) $request->amount,
                ]);
        } catch (ConnectionException $e) {
            return back()->withErrors(['api' => 'Topup service unreachable. Please try again.']);
        }

        if ($response->successful()) {
            return redirect()->route('dashboard')->with('success', 'Topup successful!');
        }

        // Handle unauthorized/forbidden - session expired
        if (in_array($response->status(), [401, 403], true)) {
            $request->session()->forget('api_token');
            return redirect()->route('login')->withErrors(['api' => 'Your session expired. Please login again.']);
        }

        // Handle other errors
        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Topup failed.') : 'Topup failed.';

        $fieldErrors = (is_array($body) && isset($body['errors']) && is_array($body['errors']))
            ? $body['errors']
            : [];

        return back()->withErrors($fieldErrors ?: ['api' => $message]);
    }
}
