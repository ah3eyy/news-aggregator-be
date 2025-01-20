<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBearerToken
{
    /**
     * Handle an incoming request.
     * This allows authenticating user for public routes that authenticated users can access
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the Bearer token is present in the Authorization header
        $token = $request->bearerToken();
        // If the Bearer token is provided, attempt to authenticate the user
        if ($token) {
            try {
                $tokenId = (new Parser(new JoseEncoder()))->parse($token)->claims()->all()['jti'];
             
                // Attempt to validate the token
                $passportToken = Token::where('id', $tokenId)
                    ->where('revoked', false)
                    ->first();

                // If a valid token exists, fetch the associated user
                if ($passportToken) {
                    $user = User::find($passportToken->user_id);
                    if ($user) {
                        Auth::setUser($user); // Authenticate the user in the current session
                    }
                }
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return $next($request);
            }
        }

        return $next($request);
    }
}
