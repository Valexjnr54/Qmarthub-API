@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/foodvendor/create" class="btn btn-outline-primary">Add New Product</a><br><br>
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
                  <th>Vendor Name</th>
                  <th>Logo</th>
                  <th>Location</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($foodVendor) > 0)
                <?php $count =0;?>
                @foreach ($foodVendor as $foodVendor)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $foodVendor->name !!}</td>
                  <td>
                    <img alt="image" src="../../storage/images/foodVendor_images/{{ $foodVendor->image }}" width="75">
                  </td>
                  <td>{!! \Illuminate\Support\Str::limit(strip_tags($foodVendor->location), 55) !!}</td>
                  <td>
                    <a href="/dashboard/admin/foodvendor/{{  $foodVendor->id  }}/edit" class="btn btn-warning">Edit</a>
                            {!! Form::open(['action' => ['App\Http\Controllers\FoodVendorsController@destroy',$foodVendor->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
