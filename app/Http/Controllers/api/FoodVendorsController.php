<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\FoodVendor;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class FoodVendorsController extends Controller
{
    use HttpResponses;
    public function index()
    {
        $foods = FoodVendor::all();
        return response()->json($foods);
    }

    public function singleFoodVendor($id)
    {
        // $food = FoodVendor::find($id);
        // return response()->json($food);
        $vendor = FoodVendor::where('id',  "$id")
                            ->orWhere('slug', "$id")
                            ->get();
        return response()->json($vendor);
    }

    public function searchFoodVendor(Request $request)
    {
        $this->validate($request,[
            'query' => 'required',
        ]);
        $query = $request->input('query');
        $vendorsearch = FoodVendor::where('name', 'like', "%$query%")
                           ->orWhere('slug', 'like', "%$query%")
                           ->orWhere('location', 'like', "%$query%")
                           ->get();
        return response()->json($vendorsearch);
    }
    public function foodByVendor($id)
    {
        $vendorfood = Food::where('vendor_id', "$id")->get();
        return response()->json($vendorfood);
    }
}
