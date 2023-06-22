<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExtrasController extends Controller
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
        $extras = Extra::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.extra')->with(['extras' => $extras]);
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
        return view('admin.create-extra');
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
        $extra = new Extra();
        $extra->name = $request->input('name');
        $extra->price = $request->input('price');
        $extra->slug = $slug;
        $extra->save();
        return redirect('./dashboard/admin/extra')->with(['success' =>'Extra has been created Successfully','title'=>'Create Extra']);

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
        $extra = Extra::find($id);
        return view('admin.edit-extra')->with(['extra'=>$extra]);
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
        $extra = Extra::find($id);
        $extra->name = $request->input('name');
        $extra->slug = $slug;
        $extra->price = $request->input('price');

        $extra->save();
        return redirect('./dashboard/admin/extra')->with(['success'=>'Extra has been updated Successfully','title'=>'Update Extra']);
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
        $extra = Extra::find($id);
        $extra->delete();
        return redirect('./dashboard/admin/extra')->with(['success'=>'Extra has been deleted Successfully','title'=>'Delete Extra']);
    }
}
