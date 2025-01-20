<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ResponseTrait;

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $user->settings;

        return $this->successResponse($user, 'User Profile');
    }

    public function savePreference(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->input();

        $user = $request->user();

        foreach ($payload as $key => $value) {
            if (!$userSetting = UserSetting::where(['user_id' => $user->id, 'key' => $key])->first()) {
                $userSetting = new UserSetting();
                $userSetting->user_id = $user->id;
                $userSetting->key = $key;
                $userSetting->value = $value;
                $userSetting->save();
            } else {
                Log::info($key);
                $userSetting->value = $value;
                $userSetting->save();
            }
        }

        return $this->successResponse([], 'Settings saved successfully.');
    }
}
