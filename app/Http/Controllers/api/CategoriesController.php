<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function singleCategory($id)
    {
        $category = Category::where('id',  "$id")
                    ->orWhere('slug', "$id")
                    ->get();
        return response()->json($category);
    }

    public function searchCategory(Request $request)
    {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $categorysearch = Category::where('category_name', 'like', "%$query%")
                           ->orWhere('slug', 'like', "%$query%")
                           ->orWhere('description', 'like', "%$query%")
                           ->get();
        return response()->json($categorysearch);
    }

    public function productByCategory($id)
    {
        $category = Category::where('id',  "$id")
                            ->orWhere('slug', "$id")
                            ->first();
        $categoryId = $category->id;
        $products = DB::select('SELECT * FROM `products` INNER JOIN `category_product` ON `category_product`.`category_id` = ? WHERE `category_product`.`product_id` = `products`.`id`', [$categoryId]);
        return response()->json($products);
    }
}
