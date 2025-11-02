<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Role;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class LoginController extends Controller
{
  protected $jwtConfig;

  public function __construct()
  {
    // JWT configuration using secret from .env
    $this->jwtConfig = Configuration::forSymmetricSigner(
      new Sha256(),
      InMemory::plainText(env('JWT_SECRET'))
    );
  }

  public function showLoginForm()
  {
    return view('auth.login');
  }

  public function login(Request $request)
  {
    // 1️⃣ Validate input
    $validator = Validator::make($request->all(), [
      'username' => 'required',
      'password' => 'required|min:6',
    ]);

    if ($validator->fails()) {
      return redirect()->route('login')
        ->withErrors($validator)
        ->withInput($request->except('password'));
    }

    // 2️⃣ Attempt login with username or email
    $credentialsUsername = ['username' => $request->username, 'password' => $request->password];
    $credentialsEmail    = ['email' => $request->username, 'password' => $request->password];

    if (!(Auth::attempt($credentialsUsername) || Auth::attempt($credentialsEmail))) {
      return redirect()->route('login')
        ->withErrors(['message' => 'Username or password was invalid.']);
    }

    $user = Auth::user();

    // 3️⃣ Check existing JWT or create new if expired
    $token = $user->access_token;

    if ($token) {
      try {
        $jwt = $this->jwtConfig->parser()->parse($token);
        $exp = $jwt->claims()->get('exp');

        // Expired → create new
        if ($exp instanceof DateTimeImmutable && $exp->getTimestamp() <= time()) {
          $token = $this->createToken($user->email);
          $user->access_token = $token;
          $user->save();
        }
      } catch (\Exception $e) {
        // Invalid token → create new
        $token = $this->createToken($user->email);
        $user->access_token = $token;
        $user->save();
      }
    } else {
      // No token → create new
      $token = $this->createToken($user->email);
      $user->access_token = $token;
      $user->save();
    }

    // 4️⃣ Store token in cookie (7 days)
    $cookie = cookie('jwt', $token, 60 * 24 * 7, null, null, false, true);
    // last "true" => HTTPOnly, secure=false for local dev

    // 5️⃣ Role-based redirect
    $role = Role::find($user->role_id);

    switch ($role->permission_level) {
      case 2:
        return redirect()->route('super.dashboard')->cookie($cookie);
      case 1:
      case 0:
      default:
        return redirect()->route('home', ['groupId' => $user->primary_group_id])->cookie($cookie);
    }
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

  public function refresh($message = "Your session may have ended, please log in.")
  {
    if (Auth::check()) {
      Auth::logout();
    }

    return redirect()->route('login')->with('message', $message);
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $cookie = Cookie::forget('jwt');

    return redirect()->route('login')->withCookie($cookie);
  }
}
