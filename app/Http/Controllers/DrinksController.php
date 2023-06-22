<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DrinksController extends Controller
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
        $drinks = Drink::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.drinks')->with(['drinks' => $drinks]);
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
        return view('admin.create-drink');
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
            //'image' => 'image|nullable|max:9999'
        ]);
        $slug = Str::slug($request->input('name'));
        $drink = new Drink();
        $drink->name = $request->input('name');
        $drink->price = $request->input('price');
        $drink->slug = $slug;
        $drink->save();
        return redirect('./dashboard/admin/drink')->with(['success' =>'Drink has been created Successfully','title'=>'Create Drink']);

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
        $drink = Drink::find($id);
        return view('admin.edit-drink')->with(['drink'=>$drink]);
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
            'price' => 'required'
        ]);



        $slug = Str::slug($request->input('name'));
        $drink = Drink::find($id);
        $drink->name = $request->input('name');
        $drink->slug = $slug;
        $drink->price = $request->input('price');

        $drink->save();
        return redirect('./dashboard/admin/drink')->with(['success'=>'Drink has been updated Successfully','title'=>'Update Drink']);
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
        $drink = Drink::find($id);
        $drink->delete();
        return redirect('./dashboard/admin/drink')->with(['success'=>'Drink has been deleted Successfully','title'=>'Delete Drink']);
    }
}
