<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description ?? '',
            'price' => (int) $product->price,
            'image' => $product->image_url,
            'images' => $this->buildImagesArray($product),
            'category_name' => $product->category?->name ?? '',
            'category_slug' => $product->category?->slug ?? '',
            'weight' => $product->weight ? $product->weight . ' Kg' : '',
            'age' => $product->age ?? '',
            'sex' => $product->sex ?? '',
            'sex_label' => $product->sex_label,
            'origin' => $product->origin ?? '',
            'condition' => $product->condition ?? '',
            'status' => $product->status ?? '',
            'status_label' => $product->status_label,
        ]);
    }

    private function buildImagesArray(Product $product): array
    {
        $allImages = $product->images ?? [];
        if ($product->image) {
            array_unshift($allImages, $product->image);
        }
        $allImages = array_filter(array_unique($allImages));

        // Convert to URLs
        return array_map(function ($img) {
            if (str_starts_with($img, 'http')) {
                return $img;
            }
            return asset('storage/' . $img);
        }, array_values($allImages));
    }
}
