@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4>Create New Product</h4>
        </div>
        <div class="card-body">
            {!! Form::open(['action' => ['App\Http\Controllers\ProductsController@update',$product->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
            <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="title">Product Name</label>
                  {{ Form::text('title',$product->product_name,['class' => 'form-control','placeholder' => 'Product Name']) }}
                </div>
                <div class="form-group col-md-6">
                  <label for="sku">SKU</label>
                  {{ Form::text('sku',$product->sku,['class' => 'form-control','placeholder' => 'Product SKU']) }}
                </div>
              </div>
              <div class="form-group">
                <label for="content">Short Description</label>
                {{ Form::textarea('content',$product->content,['class' => 'form-control summernote','placeholder' => 'Product Content']) }}
              </div>
              <div class="form-group">
                <label for="description">Long Description</label>
                {{ Form::textarea('description',$product->description,['class' => 'form-control summernote','placeholder' => 'Product Description']) }}
              </div>
              <div class="form-row">
                <div class="form-group col-md-5">
                  <label for="Price">Price</label>
                  {{ Form::text('price',$product->price,['class' => 'form-control','placeholder' => 'Product Price']) }}
                </div>
                <div class="form-group col-md-4">
                    <label for="Sales Price"> Sales Price</label>
                    {{ Form::text('sales_price',$product->sales_price,['class' => 'form-control','placeholder' => 'Product Sales Price']) }}
                </div>
                <div class="form-group col-md-3">
                  <label for="stock">Stock</label>
                  {{ Form::text('stock',$product->stock_status,['class' => 'form-control','placeholder' => 'Product Stock']) }}
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="category">Category</label>
                    <select class="form-control selectric" name="product_category[]" multiple required>
                        @if (count($categories) > 0)
                        @foreach ($categories as $category)
                            <option value="{!! $category->id !!}">{!! $category->category_name !!}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="brand">Brand</label>
                    <select class="form-control selectric" name="product_brand" required>
                        @if (count($brands) > 0)
                        @foreach ($brands as $brand)
                            <option value="{!! $brand->id !!}">{!! $brand->brand_name !!}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status">Status</label>
                    <select class="form-control selectric" name="status" required>
                        <option value="Published">Publish</option>
                        <option value="Drafted">Draft</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="image">Product Image</label>
                    <div id="image-preview" class="image-preview">
                        <label for="image-upload" id="image-label">Choose File</label>
                        <input type="file" name="image" id="image-upload" />
                    </div>                </div>
              </div>
              {{ Form::submit('Update Product',['class' => 'btn btn-primary']) }}
              {!! Form::hidden('_method','PUT') !!}
            {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
@endsection
