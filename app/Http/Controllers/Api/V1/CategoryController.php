<?php

namespace App\Http\Controllers\Api\V1;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    /**
     * List all categories with product counts.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $query = Category::where('business_id', $business_id)
            ->where('parent_id', 0)
            ->groupBy('name')
            ->withCount(['products']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->get();

        $data = $categories->map(function($category) {
            return [
                'id' => (string)$category->id,
                'name' => $category->name,
                'productCount' => (int)$category->products_count
            ];
        });

        return $this->success($data);
    }
}
