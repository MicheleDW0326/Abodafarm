<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status for a product.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
                'is_favorited' => false,
            ], 401);
        }

        $productId = $request->product_id;
        $userId = auth()->id();

        $isFavorited = Favorite::toggle($userId, Product::class, $productId);

        return response()->json([
            'success' => true,
            'message' => $isFavorited ? 'Produk ditambahkan ke wishlist.' : 'Produk dihapus dari wishlist.',
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Check if a product is favorited.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => true,
                'is_favorited' => false,
            ]);
        }

        $isFavorited = Favorite::isFavorited(
            auth()->id(),
            Product::class,
            $request->product_id
        );

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * Get wishlist count for current user.
     */
    public function count(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => true,
                'count' => 0,
            ]);
        }

        $count = auth()->user()->favoriteProducts()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
