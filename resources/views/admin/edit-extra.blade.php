@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Edit extra</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => ['App\Http\Controllers\ExtrasController@update',$extra->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group row mb-4">
                    {{ Form::label('name', 'Name',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('name',$extra->name,['class' => 'form-control','placeholder' => 'Name']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('price', 'Price',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::number('price',$extra->price,['class' => 'form-control','placeholder' => 'Price']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                        {{ Form::submit('Edit Extra',['class' => 'btn btn-primary']) }}
                    </div>
                </div>
                {!! Form::hidden('_method','PUT') !!}
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
