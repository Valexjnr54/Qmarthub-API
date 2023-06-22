@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Edit Vendor</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => ['App\Http\Controllers\FoodVendorsController@update',$foodVendor->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group row mb-4">
                    {{ Form::label('name', 'Vendor Name',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('name',$foodVendor->name,['class' => 'form-control','placeholder' => 'Vendor Name']) }}
                    </div>
                </div>

                <div class="form-group row mb-4">
                    {{ Form::label('location', 'Location',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('location',$foodVendor->location,['class' => 'form-control','placeholder' => 'Vendor Location']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('opening', 'Opening Time',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('opening',$foodVendor->opening_at,['class' => 'form-control timepicker','placeholder' => 'Opening Time']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('closing', 'Closing Time',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('closing',$foodVendor->closing_at,['class' => 'form-control timepicker','placeholder' => 'Closing Time']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Logo</label>
                    <div class="col-sm-12 col-md-9">
                    <div id="image-preview" class="image-preview">
                        <label for="image-upload" id="image-label">Choose File</label>
                        <input type="file" name="image" id="image-upload" />
                    </div>
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                        {{ Form::submit('Edit Vendor',['class' => 'btn btn-primary']) }}
                    </div>
                </div>
                {!! Form::hidden('_method','PUT') !!}
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
