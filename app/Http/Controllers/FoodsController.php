<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Food;
use App\Models\FoodDrink;
use App\Models\FoodExtra;
use App\Models\FoodVendor;

class FoodsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $foods = Food::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $vendors = FoodVendor::all();
        //return $vendor;
        return view('admin.foods')->with(['foods'=>$foods,'vendors'=>$vendors]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $foodVendor = FoodVendor::all();
        $drinks = Drink::all();
        $extras = Extra::all();
        return view('admin.create-food')->with([
            'foodVendor' => $foodVendor,
            'drinks' => $drinks,
            'extras' => $extras
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
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $this->validate($request,[
            'name' => 'required',
            'price' => 'required',
            'vendor_id' => 'required',
            //'image' => 'image|nullable|max:9999'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/food_images',$fileNameToStore);
        }else{
            $fileNameToStore = 'noImage.jpg';
        }

        $slug = Str::slug($request->input('name'));


        $food = new Food;
        $food->name = $request->input('name');
        $food->description = $request->input('description');
        $food->price = $request->input('price');
        $food->slug = $slug;
        $food->vendor_id = $request->input('vendor_id');
        $food->picture = $fileNameToStore;
        $food->save();

        $foodId = Food::all()->last()->id;


        if ($request->has('food_drink') && $request->has('food_extra')) {
            $drink_array = $request->input('food_drink');
            $extra_array = $request->input('food_extra');
            foreach ($drink_array as $value) {
                $foodDrink = new FoodDrink();
                $foodDrink->food_id = $foodId;
                $foodDrink->drink_id = $value;
                $foodDrink->save();
            }
            foreach ($extra_array as $value) {
                $foodExtra = new FoodExtra();
                $foodExtra->food_id = $foodId;
                $foodExtra->extra_id = $value;
                $foodExtra->save();
            }
        }elseif ($request->has('food_drink')) {
            $drink_array = $request->input('food_drink');
            foreach ($drink_array as $value) {
                $foodDrink = new FoodDrink();
                $foodDrink->food_id = $foodId;
                $foodDrink->drink_id = $value;
                $foodDrink->save();
            }
        }elseif ($request->has('food_extra')) {
            $extra_array = $request->input('food_extra');
            foreach ($extra_array as $value) {
                $foodExtra = new FoodExtra();
                $foodExtra->food_id = $foodId;
                $foodExtra->extra_id = $value;
                $foodExtra->save();
            }
        }else {}

        return redirect('./dashboard/admin/food')->with(['success' =>'Food has been created Successfully','title'=>'Create Food']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $food = Food::find($id);
        $foodVendor = FoodVendor::all();
        $drinks = Drink::all();
        $extras = Extra::all();
        return view('admin.edit-food')->with([
            'foodVendor'=>$foodVendor,
            'food'=>$food,
            'drinks' => $drinks,
            'extras' => $extras
        ]);
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
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $this->validate($request,[
            'name' => 'required',
            'price' => 'required',
            'vendor_id' => 'required'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/food_images',$fileNameToStore);
        }



        $slug = Str::slug($request->input('name'));
        $food = Food::find($id);
        $food->name = $request->input('name');
        $food->description = $request->input('description');
        $food->slug = $slug;
        $food->price = $request->input('price');
        $food->vendor_id = $request->input('vendor_id');
        if($request->hasFile('image')){
            $food->picture = $fileNameToStore;
        }
        $food->save();
        $foodId = $food->id;
        if ($request->has('food_drink') && $request->has('food_extra')) {
            $drink_array = $request->input('food_drink');
            $extra_array = $request->input('food_extra');
            foreach ($drink_array as $value) {
                if (FoodDrink::where('drink_id','=',$value)->exists()) {
                    # code...
                } else {
                    $foodDrink = new FoodDrink();
                    $foodDrink->food_id = $foodId;
                    $foodDrink->drink_id = $value;
                    $foodDrink->save();
                }
            }
            foreach ($extra_array as $value) {
                if (FoodExtra::where('extra_id','=',$value)->exists()) {
                    # code...
                } else {
                    $foodExtra = new FoodExtra();
                $foodExtra->food_id = $foodId;
                $foodExtra->extra_id = $value;
                $foodExtra->save();
                }
            }
        }elseif ($request->has('food_drink')) {
            $drink_array = $request->input('food_drink');
            foreach ($drink_array as $value) {
                if (FoodDrink::where('drink_id','=',$value)->exists()) {
                    # code...
                } else {
                    $foodDrink = new FoodDrink();
                    $foodDrink->food_id = $foodId;
                    $foodDrink->drink_id = $value;
                    $foodDrink->save();
                }
            }
        }elseif ($request->has('food_extra')) {
            $extra_array = $request->input('food_extra');
            foreach ($extra_array as $value) {
                if (FoodExtra::where('extra_id','=',$value)->exists()) {
                    # code...
                } else {
                    $foodExtra = new FoodExtra();
                $foodExtra->food_id = $foodId;
                $foodExtra->extra_id = $value;
                $foodExtra->save();
                }
            }
        }else {}
        return redirect('./dashboard/admin/food')->with(['success'=>'Food has been updated Successfully','title'=>'Update Food']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        $food = Food::find($id);
        if ($food->picture != 'noImage.jpg') {
            Storage::delete('public/images/food_images/'.$food->picture);
        }
        $food->delete();
        return redirect('./dashboard/admin/food')->with(['success'=>'Food has been deleted Successfully','title'=>'Delete Food']);
    }
}
