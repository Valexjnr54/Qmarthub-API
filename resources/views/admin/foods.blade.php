@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/food/create" class="btn btn-outline-primary">Add New Product</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Food Vendors</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>Food Name</th>
                  <th>Image</th>
                  <th>Price</th>
                  <th>Vendor</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($foods) > 0)
                <?php $count =0;?>
                @foreach ($foods as $food)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $food->name !!}</td>
                  <td>
                    <img alt="image" src="../../storage/images/food_images/{{ $food->picture }}" width="75">
                  </td>
                  <td>{!! \Illuminate\Support\Str::limit(strip_tags($food->price), 55) !!}</td>
                  <td>
                    @if (count($vendors) > 0)
                    @foreach ($vendors as $vendor)
                        @if ($vendor->id == $food->vendor_id)
                            {!! $vendor->name !!}
                        @endif
                    @endforeach
                    @endif
                  </td>
                  <td>
                    <a href="/dashboard/admin/food/{{  $food->id  }}/edit" class="btn btn-warning">Edit</a>
                            {!! Form::open(['action' => ['App\Http\Controllers\FoodsController@destroy',$food->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
