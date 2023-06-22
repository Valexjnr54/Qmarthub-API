<?php

namespace App\Http\Controllers;

use App\Models\Bulk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BulkController extends Controller
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
        $bulks = Bulk::all();
        if (auth()->user()->id !== 1) {
            return redirect('/');
        }
        return view('admin.bulk')->with('bulks',$bulks);
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
        return view('admin.create-bulk');
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
            'discount' => 'required',
        ]);


        $bulk = new Bulk;
        $bulk->name = $request->input('name');
        $bulk->price = $request->input('price');
        $bulk->discount = $request->input('discount');
        $bulk->save();
        return redirect('./dashboard/admin/bulk')->with(['success' =>'Bulk has been created Successfully','title'=>'Create Bulk Item']);
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
        $bulk = Bulk::find($id);
        return view('admin.edit-bulk')->with('bulk',$bulk);
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
            'discount' => 'required',
        ]);


        $bulk = Bulk::find($id);
        $bulk->name = $request->input('name');
        $bulk->price = $request->input('price');
        $bulk->discount = $request->input('discount');
        $bulk->save();
        return redirect('./dashboard/admin/bulk')->with(['success'=>'Bulk has been updated Successfully','title'=>'Update Bulk Item']);
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
        $bulk = Bulk::find($id);
        $bulk->delete();
        return redirect('./dashboard/admin/bulk')->with(['success'=>'bulk has been deleted Successfully','title'=>'Delete bulk']);
    }
}
