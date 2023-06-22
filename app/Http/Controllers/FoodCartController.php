<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Food;

class FoodCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return view('cart-food')->with([
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $duplicates = Cart::instance('food')->search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->id;
        });
        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.food')->with(['warning'=> 'Food is already in your cart!','title' => 'Food Already in Cart']);
        }
        $food = Food::find($request->id);
        $image = $food->picture;

        Cart::instance('food')->add(array('id'=>$request->id, 'name'=>$request->name, 'qty'=>$request->qty, 'price'=>$request->price, 'options' => ['image' => $image]))
            ->associate('App\Food');
         return back()->with(['success' => 'Food was added to your cart!','title' => 'Add Food to Cart']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Cart::instance('food')->update($id, $request->quantity);
        session()->flash(['successMessage'=> 'Food Quantity was updated successfully!','title'=> 'Update Food from Cart']);
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cart::instance('food')->remove($id);

        return back()->with(['success'=> 'Food has been removed!','title' => 'Remove Food from Cart']);
    }
}
