<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductsController extends Controller
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
        $product = Product::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.product')->with('product',$product);
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
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.create-product')->with(['categories'=>$categories,'brands'=>$brands]);
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
            'sku' => 'required',
            'content' => 'required',
            'description' => 'required',
            'price' => 'required',
            'sales_price' => 'nullable',
            'stock' => 'required',
            'product_category' => 'required',
            'product_brand' => 'required',
            'status' => 'required',
            //'image' => 'image|nullable|max:9999'
        ]);

        // $input_array = $request->input('product_category');
        // foreach ($input_array as $value) {

        // }



        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/product_images',$fileNameToStore);
            $imageURL = 'https://qmarthub.com/storage/images/product_images/'.$fileNameToStore;
        }else{
            $fileNameToStore = 'noImage.jpg';
            $imageURL = 'https://qmarthub.com/storage/images/product_images/'.$fileNameToStore;
        }
        $slug = Str::slug($request->input('title'));


        $product = new Product;
        $product->product_name = $request->input('title');
        $product->description = $request->input('description');
        $product->content = $request->input('content');
        $product->slug = $slug;
        $product->sku = $request->input('sku');
        $product->price = $request->input('price');
        $product->sales_price = $request->input('sales_price');
        $product->stock_status = $request->input('stock');
        $product->status = $request->input('status');
        $product->brand_id= $request->input('product_brand');
        $product->product_image = $fileNameToStore;
        $product->image_url = $imageURL;
        $product->save();

        // $productDetail = Product::latest()->first();
        $productId = Product::all()->last()->id;

        if ($request->has('product_category')) {
            $input_array = $request->input('product_category');
            foreach ($input_array as $value) {
                $categoryProduct = new CategoryProduct();
                $categoryProduct->product_id = $productId;
                $categoryProduct->category_id = $value;
                $categoryProduct->save();
            }
        }

        return redirect('./dashboard/admin/product')->with(['success' =>'Product has been created Successfully','title'=>'Create Product']);
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

        $product = product::find($id);
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.edit-product')->with(['product'=>$product,'categories'=>$categories,'brands'=>$brands]);
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
            'sku' => 'required',
            'content' => 'required',
            'description' => 'required',
            'price' => 'required',
            'sales_price' => 'nullable',
            'stock' => 'required',
            'product_category' => 'required',
            'product_brand' => 'required',
            'status' => 'required'
        ]);

        if($request->hasFile('image')){
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $ext = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$ext;
            $path = $request->file('image')->storeAs('public/images/product_images',$fileNameToStore);
            $imageURL = 'https://qmarthub.com/storage/images/product_images/'.$fileNameToStore;
        }
        $slug = Str::slug($request->input('title'));
        $product = Product::find($id);
        $product->product_name = $request->input('title');
        $product->description = $request->input('description');
        $product->content = $request->input('content');
        $product->slug = $slug;
        $product->sku = $request->input('sku');
        $product->price = $request->input('price');
        $product->sales_price = $request->input('sales_price');
        $product->stock_status = $request->input('stock');
        $product->status = $request->input('status');
        // $product->category_id = $request->input('product_category');
        $product->brand_id = $request->input('product_brand');
        if($request->hasFile('image')){
            $product->product_image = $fileNameToStore;
            $product->image_url = $imageURL;
        }
        $product->save();

        $categoryProduct = CategoryProduct::where('product_id',$id)->first();
        $categoryProductProductId = $categoryProduct->product_id;
        $categoryProductId = $categoryProduct->id;


        if ($request->has('product_category')) {
            if ($id == $categoryProductProductId) {
                $input_array = $request->input('product_category');
                foreach ($input_array as $value) {
                    if (CategoryProduct::where('category_id','=',$value)->exists()) {
                        // return redirect('./dashboard/admin/product')->with(['success'=>'Product has been updated Successfully','title'=>'Update Product']);
                    } else {
                        $catego = CategoryProduct::find($categoryProductId);
                        $catego->category_id = $value;
                        $catego->save();
                    }
                }
            }
        }

        return redirect('./dashboard/admin/product')->with(['success'=>'Product has been updated Successfully','title'=>'Update Product']);
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
        $product = Product::find($id);
        if ($product->product_image != 'noImage.jpg') {
            Storage::delete('public/images/product_images/'.$product->product_image);
        }
        $product->delete();
        return redirect('./dashboard/admin/product')->with(['success'=>'Product has been deleted Successfully','title'=>'Delete Product']);
    }
}
