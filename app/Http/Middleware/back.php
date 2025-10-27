<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;

class AuthGroupUser
{
    protected $jwtConfig;

    public function __construct()
    {
        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(env('JWT_SECRET'))
        );
    }

    public function handle(Request $request, Closure $next)
    {
        // =========================
        // 1️⃣ Web user token sync
        // =========================
        if (Auth::check()) {
            $user = Auth::user();

            // Only create a token if missing
            if (empty($user->access_token)) {
                $user->access_token = $this->createToken($user->email);
                $user->save();
            }

            // Set cookie for API calls
            cookie()->queue('jwt', $user->access_token, 60 * 24 * 7, '/', null, false, true);
        }

        // =========================
        // 2️⃣ API authentication
        // =========================
        if ($request->is('api/*')) {
            // Prefer Bearer token from header
            $token = $request->bearerToken();

            // Fallback: cookie
            if (!$token && $request->hasCookie('jwt')) {
                $token = $request->cookie('jwt');
            }

            if (!$token) {
                return response()->json(['error' => 'Access denied. No token provided.'], 403);
            }

            try {
                // Decode token safely
                $jwt = $this->jwtConfig->parser()->parse((string) $token);

                // Get exp claim
                $exp = $jwt->claims()->get('exp');

                if (!$exp instanceof DateTimeImmutable || $exp <= new DateTimeImmutable()) {
                    return response()->json(['error' => 'Token expired.'], 403);
                }
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Invalid token.'], 403);
            }
        }

        return $next($request);
    }

    protected function createToken($email)
    {
        $now = new DateTimeImmutable();
        $exp = $now->modify('+1 week');

        return $this->jwtConfig->builder()
            ->issuedAt($now)
            ->expiresAt($exp)
            ->withClaim('email', $email)
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();
    }
}
