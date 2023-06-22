@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Create New Category</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => 'App\Http\Controllers\FoodsController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group row mb-4">
                    {{ Form::label('name', 'Food Name',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('name','',['class' => 'form-control','placeholder' => 'Food Name']) }}
                    </div>
                </div>

                <div class="form-group row mb-4">
                    {{ Form::label('vendor', 'Food Vendor',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        <select class="form-control selectric" name="vendor_id" required>
                            <option value="">-- Choose A Vendor --</option>
                            @if (count($foodVendor) > 0)
                            @foreach ($foodVendor as $foodVendor)
                                <option value="{!! $foodVendor->id !!}">{!! $foodVendor->name !!}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('price', 'Food Price',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::number('price','',['class' => 'form-control','placeholder' => 'Food Price']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('drinks', 'Drinks',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        <select class="form-control selectric" name="food_drink[]" multiple>
                            <option value="">-- Choose Drinks --</option>
                            @if (count($drinks) > 0)
                            @foreach ($drinks as $drink)
                                <option value="{!! $drink->id !!}">{!! $drink->name !!}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('toppings', 'Toppings/Extras',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        <select class="form-control selectric" name="food_extra[]" multiple>
                            <option value="">-- Choose Toppings --</option>
                            @if (count($extras) > 0)
                            @foreach ($extras as $extra)
                                <option value="{!! $extra->id !!}">{!! $extra->name !!}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group row mb-4">
                    {{ Form::label('description', 'Food Description',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::textarea('description','',['class' => 'form-control summernote','placeholder' => 'Vendor Location']) }}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Food Picture</label>
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
                        {{ Form::submit('Create Food',['class' => 'btn btn-primary']) }}
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
