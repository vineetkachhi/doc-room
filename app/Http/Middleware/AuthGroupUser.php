<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
    if (Auth::check() && !$request->is('api/*')) {
      $user = Auth::user();

      // Only create a token if missing
      if (empty($user->access_token)) {
        $user->access_token = $this->createToken($user->email);
        $user->save();
      }

      // Set cookie for API calls (HTTP only)
      cookie()->queue('jwt', $user->access_token, 60 * 24 * 7, '/', null, false, true);
    }

    // =========================
    // 2️⃣ API authentication
    // =========================
    if ($request->is('api/*')) {
      $token = null;

      // Get JWT from cookie
      if ($request->hasCookie('jwt')) {
        try {
          // Decrypt if Laravel encrypted cookies
          $token = Crypt::decryptString($request->cookie('jwt'));
        } catch (\Throwable $e) {
          return response()->json(['error' => 'Invalid token.'], 403);
        }
      }

      if (!$token) {
        return response()->json(['error' => 'Access denied. No token provided.'], 403);
      }

      // Extract actual JWT from "hash|JWT" format
      $tokenParts = explode('|', $token);
      $jwtString = end($tokenParts); // take last part (the actual JWT)

      try {
        // Parse JWT
        $jwt = $this->jwtConfig->parser()->parse($jwtString);

        // Check expiration
        $exp = $jwt->claims()->get('exp');
        if (!$exp instanceof DateTimeImmutable || $exp->getTimestamp() <= time()) {
          return response()->json(['error' => 'Token expired.'], 403);
        }

        // Attach email from JWT to request
        $request->merge(['jwt_email' => $jwt->claims()->get('email')]);
      } catch (\Throwable $e) {
        return response()->json(['error' => 'Invalid token.'], 403);
      }
    }

    return $next($request);
  }

  protected function createToken($email)
  {
    $now = new DateTimeImmutable();
    $exp = $now->modify('+7 days');

    return $this->jwtConfig->builder()
      ->issuedAt($now)
      ->expiresAt($exp)
      ->withClaim('email', $email)
      ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
      ->toString();
  }
}
