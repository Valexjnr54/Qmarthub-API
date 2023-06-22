@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Create New Category</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => 'App\Http\Controllers\CategoriesController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                <div class="form-group row mb-4">
                    {{ Form::label('title', 'Title',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::text('title','',['class' => 'form-control','placeholde' => 'Category Title']) }}
                    </div>
                </div>

                <div class="form-group row mb-4">
                    {{ Form::label('description', 'Description',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                        {{ Form::textarea('description','',['class' => 'form-control summernote','placeholde' => 'Category Description']) }}
                    {{-- <textarea class="summernote" name="description" required></textarea> --}}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Image</label>
                    <div class="col-sm-12 col-md-9">
                    <div id="image-preview" class="image-preview">
                        <label for="image-upload" id="image-label">Choose File</label>
                        <input type="file" name="image" id="image-upload" />
                    </div>
                    </div>
                </div>

                <div class="form-group row mb-4">
                    {{ Form::label('status', 'Status',['class' => 'col-form-label text-md-right col-12 col-md-3 col-lg-3']) }}
                    <div class="col-sm-12 col-md-7">
                    <select class="form-control selectric" name="status" required>
                        <option value="Published">Publish</option>
                        <option value="Drafted">Draft</option>
                        <option value="Pending">Pending</option>
                    </select>
                    {{-- {{ Form::select('size', ['Larget' => 'Large']) }} --}}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                        {{ Form::submit('Create Category',['class' => 'btn btn-primary']) }}
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
