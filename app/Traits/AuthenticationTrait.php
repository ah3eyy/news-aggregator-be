<?php

namespace App\Traits;

use App\Models\User;

trait AuthenticationTrait
{
    public function createAuthenticationToken(User $user): string
    {
        return $user->createToken('Access Token')->accessToken;
    }

}
