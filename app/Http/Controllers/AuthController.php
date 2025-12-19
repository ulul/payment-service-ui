<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(LoginRequest $request)
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(config('app.api_call_timeout'))
                ->post(rtrim(config('app.api_url'), '/') .  '/api/auth/login', [
                    'email' => $request->email,
                    'password' => $request->password
                ]);
        } catch (ConnectionException $th) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['api' => 'Auth service unreachable. Please try again.']);
        }

         if ($response->successful()) {
            $token = $response->json('token');

            if (!$token) {
                return back()
                    ->withInput($request->except('password'))
                    ->withErrors(['api' => 'Login succeeded but no token was returned.']);
            }

            $request->session()->put('api_token', $token);
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Login successful.');
        }

        // Upstream API returned 4xx/5xx
        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Login failed.') : 'Login failed.';

        // If upstream provides field errors like { errors: { email: [...], password: [...] } }
        $fieldErrors = (is_array($body) && isset($body['errors']) && is_array($body['errors']))
            ? $body['errors']
            : [];

        // For normal browser submits => redirect back (302) with errors
        return back()
            ->withInput($request->except('password'))
            ->withErrors($fieldErrors ?: ['api' => $message]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(15)
                ->post(rtrim(config('app.api_url'), '/') . '/api/auth/register', [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                ]);
        } catch (ConnectionException $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['api' => 'Auth service unreachable. Please try again.']);
        }

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Register successful.');
        }

        $body = $response->json();
        $message = is_array($body) ? ($body['message'] ?? 'Register failed.') : 'Register failed.';

        $fieldErrors = (is_array($body) && isset($body['errors']) && is_array($body['errors']))
            ? $body['errors']
            : [];

        return back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->withErrors($fieldErrors ?: ['api' => $message]);
    }
}


