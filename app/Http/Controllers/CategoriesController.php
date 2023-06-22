<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriesController extends Controller
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
        $category = Category::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.category')->with('category',$category);
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
        return view('admin.create-category');
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
            'description' => 'required',
            'status' => 'required',
            //'image' => 'image|nullable|max:9999'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/category_images',$fileNameToStore);
        }else{
            $fileNameToStore = 'noImage.jpg';
        }

        $slug = Str::slug($request->input('title'));


        $category = new Category;
        $category->category_name = $request->input('title');
        $category->description = $request->input('description');
        $category->slug = $slug;
        $category->status = $request->input('status');
        $category->category_image = $fileNameToStore;
        $category->save();
        return redirect('./dashboard/admin/category')->with(['success' =>'Category has been created Successfully','title'=>'Create Category']);
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
        $category = Category::find($id);
        return view('admin.edit-category')->with('category',$category);
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
            'description' => 'required',
            'status' => 'required',
        ]);
        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/category_images',$fileNameToStore);
        }

        $slug = Str::slug($request->input('title'));

        $category = Category::find($id);
        $category->category_name = $request->input('title');
        $category->description = $request->input('description');
        $category->slug = $slug;
        $category->status = $request->input('status');
        if($request->hasFile('image')){
            $category->category_image = $fileNameToStore;
        }
        $category->save();
        return redirect('./dashboard/admin/category')->with(['success'=>'Category has been updated Successfully','title'=>'Update Category']);
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
        $category = Category::find($id);
        if ($category->category_image != 'noImage.jpg') {
            Storage::delete('public/images/category_images/'.$category->category_image);
        }
        $category->delete();
        return redirect('./dashboard/admin/category')->with(['success'=>'Category has been deleted Successfully','title'=>'Delete Category']);
    }
}
