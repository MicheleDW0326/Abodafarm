<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmController extends Controller
{
    /**
     * Save FCM token for authenticated user
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'device_type' => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:255',
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            FcmToken::updateOrCreateToken(
                Auth::id(),
                $request->token,
                $request->device_type ?? 'web',
                $request->device_name
            );

            return response()->json([
                'success' => true,
                'message' => 'Token saved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save token',
            ], 500);
        }
    }

    /**
     * Remove FCM token
     */
    public function removeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            FcmToken::where('user_id', Auth::id())
                ->where('token', $request->token)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove token',
            ], 500);
        }
    }

    /**
     * Get user's FCM tokens
     */
    public function getTokens()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $tokens = FcmToken::where('user_id', Auth::id())
            ->active()
            ->get(['token', 'device_type', 'device_name', 'last_used_at']);

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
        ]);
    }
}
