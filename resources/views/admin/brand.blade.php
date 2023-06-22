@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/brand/create" class="btn btn-outline-primary">Add New Product</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Brands</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>Brand Name</th>
                  <th>Logo</th>
                  <th>URL</th>
                  <th>Description</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($brand) > 0)
                <?php $count =0;?>
                @foreach ($brand as $brand)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $brand->brand_name !!}</td>
                  <td>
                    <img alt="image" src="../../storage/images/brand_images/{{ $brand->brand_image }}" width="75">
                  </td>
                  <td>{!! $brand->url !!}</td>
                  <td>{!! \Illuminate\Support\Str::limit(strip_tags($brand->description), 40) !!}</td>
                  <td>
                    <a href="/dashboard/admin/brand/{{  $brand->id  }}/edit" class="btn btn-warning">Edit</a>
                            {!! Form::open(['action' => ['App\Http\Controllers\BrandsController@destroy',$brand->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
