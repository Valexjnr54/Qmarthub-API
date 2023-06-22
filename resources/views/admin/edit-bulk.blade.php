@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Create New Category</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => ['App\Http\Controllers\BulkController@update',$bulk->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group row mb-4">
                    {{ Form::label('name', 'Name',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('name',$bulk->name,['class' => 'form-control','placeholde' => 'Bulk Item Name']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('price', 'Price',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('price',$bulk->price,['class' => 'form-control','placeholde' => 'Price']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('discount', 'Discount',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('discount',$bulk->discount,['class' => 'form-control','placeholde' => 'Discount']) }}
                    </div>
                </div>

                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                        {{ Form::submit('Edit Bulk Item',['class' => 'btn btn-primary']) }}
                    </div>
                </div>
                {!! Form::hidden('_method','PUT') !!}
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
