<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BrandsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        $brand = Brand::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.brand')->with('brand',$brand);
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
        return view('admin.create-brand');
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
            'title' => 'required',
            'url' => 'required',
            'description' => 'required',
            'status' => 'required',
            //'image' => 'image|nullable|max:9999'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/brand_images',$fileNameToStore);
        }else{
            $fileNameToStore = 'noImage.jpg';
        }

        $slug = Str::slug($request->input('title'));


        $brand = new Brand;
        $brand->brand_name = $request->input('title');
        $brand->url = $request->input('url');
        $brand->description = $request->input('description');
        $brand->slug = $slug;
        $brand->status = $request->input('status');
        $brand->brand_image = $fileNameToStore;
        $brand->save();
        return redirect('./dashboard/admin/brand')->with(['success' =>'Brand has been created Successfully','title'=>'Create Brand']);
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
        $brand = Brand::find($id);
        return view('admin.edit-brand')->with('brand',$brand);
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
            'title' => 'required',
            'url' => 'required',
            'description' => 'required',
            'status' => 'required',
        ]);
        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/brand_images',$fileNameToStore);
        }

        $slug = Str::slug($request->input('title'));

        $brand = Brand::find($id);
        $brand->brand_name = $request->input('title');
        $brand->url = $request->input('url');
        $brand->description = $request->input('description');
        $brand->slug = $slug;
        $brand->status = $request->input('status');
        if($request->hasFile('image')){
            $brand->brand_image = $fileNameToStore;
        }
        $brand->save();
        return redirect('./dashboard/admin/brand')->with(['success'=>'Brand has been updated Successfully','title'=>'Update Brand']);
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
        $brand = Brand::find($id);
        if ($brand->brand_image != 'noImage.jpg') {
            Storage::delete('public/images/brand_images/'.$brand->brand_image);
        }
        $brand->delete();
        return redirect('./dashboard/admin/brand')->with(['success'=>'Brand has been deleted Successfully','title'=>'Delete Brand']);
    }
}
