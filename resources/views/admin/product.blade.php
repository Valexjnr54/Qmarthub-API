@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/product/create" class="btn btn-outline-primary">Add New Product</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Products</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>Product Name</th>
                  <th>Product Image</th>
                  <th>Price</th>
                  <th>Sales Price</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($product) > 0)
                <?php $count =0;?>
                @foreach ($product as $product)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                    <td>{!! $product->product_name !!}</td>
                    <td>
                        <img alt="image" src="../../storage/images/product_images/{{ $product->product_image }}" width="75">
                    </td>
                    <td>{!! $product->price !!}</td>
                    <td>{!! $product->sales_price !!}</td>
                  <td>
                    <div class="badge badge-success badge-shadow">{!! $product->status !!}</div>
                  </td>
                  <td>
                    <a href="/dashboard/admin/product/{{  $product->id  }}/edit" class="btn btn-warning pull-left" style="">Edit</a>
                        {!! Form::open(['action' => ['App\Http\Controllers\ProductsController@destroy',$product->id], 'method' => 'POST','class' => 'pull-right']) !!}
                            {{ Form::submit('Delete',['class' => 'btn btn-danger']) }}
                            {!! Form::hidden('_method','DELETE') !!}
                        {!! Form::close() !!}
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
