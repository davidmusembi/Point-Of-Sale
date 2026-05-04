<?php

namespace App\Http\Controllers\Api\V1;

use App\Product;
use App\Variation;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    /**
     * List all products (variations).
     */
    public function index(Request $request)
    {
        $query = Variation::with(['product.category', 'product.unit', 'variation_location_details']);

        // Apply location filter to the variation_location_details
        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations !== 'all') {
            $query->whereHas('variation_location_details', function($q) use ($permitted_locations) {
                $q->whereIn('location_id', $permitted_locations);
            });
        }

        if ($request->has('categoryId')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->categoryId);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sub_sku', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('perPage', 20);
        $variations = $query->paginate($perPage);

        $data = collect($variations->items())->map(function($variation) {
            return $this->formatProduct($variation);
        });

        return $this->paginate($variations, $data);
    }

    /**
     * Get single product details.
     */
    public function show($id)
    {
        $variation = Variation::with(['product.category', 'product.unit', 'variation_location_details'])->find($id);

        if (!$variation) {
            return $this->error('NOT_FOUND', 'Product not found', null, 404);
        }

        return $this->success($this->formatProduct($variation));
    }

    /**
     * Look up by barcode.
     */
    public function showByBarcode($barcode)
    {
        $variation = Variation::with(['product.category', 'product.unit', 'variation_location_details'])
            ->where('sub_sku', $barcode)
            ->first();

        if (!$variation) {
            return $this->error('NOT_FOUND', 'Product not found with this barcode', null, 404);
        }

        return $this->success($this->formatProduct($variation));
    }

    /**
     * Format variation into the simplified API Product spec.
     */
    private function formatProduct($variation)
    {
        $product = $variation->product;
        $permitted_locations = $this->getPermittedLocations();
        
        // Sum stock across permitted locations for this variation
        $stockQuantity = $variation->variation_location_details
            ->when($permitted_locations !== 'all', function($q) use ($permitted_locations) {
                return $q->whereIn('location_id', $permitted_locations);
            })
            ->sum('qty_available');

        return [
            'id' => (int)$variation->id,
            'name' => $product->type == 'variable' ? $product->name . ' (' . $variation->name . ')' : $product->name,
            'sku' => $variation->sub_sku,
            'barcode' => $variation->sub_sku,
            'category' => [
                'id' => (int)$product->category_id,
                'name' => $product->category->name ?? 'N/A'
            ],
            'sellingPrice' => (float)$variation->default_sell_price,
            'costPrice' => (float)$variation->default_purchase_price,
            'stockQuantity' => (float)$stockQuantity,
            'reorderLevel' => (float)$product->alert_quantity,
            'unit' => $product->unit->actual_name ?? 'Unit',
            'imageUrl' => $product->image_url,
            'isActive' => true,
            'updatedAt' => $variation->updated_at->toIso8601String()
        ];
    }
}
