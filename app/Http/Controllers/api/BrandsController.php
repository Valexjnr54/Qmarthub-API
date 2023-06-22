<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    public function singleBrand($id)
    {
        $brand = Brand::where('id',  "$id")
                    ->orWhere('slug', "$id")
                    ->get();
        return response()->json($brand);
    }

    public function searchBrand(Request $request)
    {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $brandsearch = Brand::where('brand_name', 'like', "%$query%")
                           ->orWhere('slug', 'like', "%$query%")
                           ->orWhere('description', 'like', "%$query%")
                           ->get();
        return response()->json($brandsearch);
    }
}
