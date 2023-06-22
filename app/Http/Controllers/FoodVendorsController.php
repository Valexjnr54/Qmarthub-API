<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\FoodVendor;

class FoodVendorsController extends Controller
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
        $foodVendor = FoodVendor::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.food-vendor')->with('foodVendor',$foodVendor);
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
        return view('admin.create-food-vendor');
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
            'location' => 'required',
            'opening' => 'required',
            'closing' => 'required',
            ///'image' => 'image|nullable|max:9999'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/foodVendor_images',$fileNameToStore);
        }else{
            $fileNameToStore = 'noImage.jpg';
        }

        $slug = Str::slug($request->input('name'));


        $foodVendor = new FoodVendor;
        $foodVendor->name = $request->input('name');
        $foodVendor->location = $request->input('location');
        $foodVendor->slug = $slug;
        $foodVendor->image = $fileNameToStore;
        $foodVendor->opening_at = $request->input('opening');
        $foodVendor->closing_at = $request->input('closing');
        $foodVendor->save();
        return redirect('./dashboard/admin/foodvendor')->with(['success' =>'Food Vendor has been created Successfully','title'=>'Create Food Vendor']);
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
        $foodVendor = FoodVendor::find($id);
        return view('admin.edit-food-vendor')->with('foodVendor',$foodVendor);
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
            'location' => 'required'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/foodVendor_images',$fileNameToStore);
        }

        $slug = Str::slug($request->input('name'));
        $foodVendor = FoodVendor::find($id);
        $foodVendor->name = $request->input('name');
        $foodVendor->slug = $slug;
        $foodVendor->location = $request->input('location');
        if($request->hasFile('image')){
            $foodVendor->image = $fileNameToStore;
        }
        $foodVendor->opening_at = $request->input('opening');
        $foodVendor->closing_at = $request->input('closing');
        $foodVendor->save();
        return redirect('./dashboard/admin/foodvendor')->with(['success'=>'Food Vendor has been updated Successfully','title'=>'Update Food Vendor']);
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
        $foodVendor = foodVendor::find($id);
        if ($foodVendor->image != 'noImage.jpg') {
            Storage::delete('public/images/foodVendor_images/'.$foodVendor->image);
        }
        $foodVendor->delete();
        return redirect('./dashboard/admin/foodvendor')->with(['success'=>'Food Vendor has been deleted Successfully','title'=>'Delete Food Vendor']);
    }
}
