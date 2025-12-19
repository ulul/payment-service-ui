<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->session()->get('api_token');

        if (!$token) {
            return redirect()->route('login')->withErrors(['api' => 'Please login to continue.']);
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->get(rtrim(config('app.api_url'), '/') . '/api/me');
        } catch (ConnectionException $e) {
            return view('dashboard', [
                'user' => null,
                'error' => 'Auth service unreachable. Please try again.',
                'status' => 503,
                'body' => null,
            ]);
        }

        if ($response->successful()) {
            return view('dashboard', [
                'user' => $response->json(),
                'error' => null,
                'status' => $response->status(),
                'body' => null,
            ]);
        }

        if (in_array($response->status(), [401, 403], true)) {
            $request->session()->forget('api_token');

            return redirect()->route('login')->withErrors(['api' => 'Your session expired. Please login again.']);
        }

        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Failed to load profile.') : 'Failed to load profile.';

        return view('dashboard', [
            'user' => null,
            'error' => $message,
            'status' => $response->status(),
            'body' => $body,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->session()->get('api_token');

        if ($token) {
           
            Http::acceptJson()
                ->withToken($token)
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') . '/api/auth/logout');
            
        }

        $request->session()->forget('api_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        

        return redirect()->route('login')->with('success', 'Logged out.');
    }
}


