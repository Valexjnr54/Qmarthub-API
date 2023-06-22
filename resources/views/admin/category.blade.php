@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="{{ route('admin.category.create') }}" class="btn btn-outline-primary">Add New Product</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Categories</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>Category Name</th>
                  <th>Category Image</th>
                  <th>Description</th>
                  <!--<th>Status</th>-->
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($category) > 0)
                <?php $count =0;?>
                @foreach ($category as $cate)
                <?php $count++;?>
                    <tr>
                        <td>
                            <?= $count?>
                        </td>
                        <td>{!! $cate->category_name !!}</td>
                        <td>
                            <img alt="image" src="../../storage/images/category_images/{{ $cate->category_image }}" width="75">
                        </td>
                        <td>{!! \Illuminate\Support\Str::limit(strip_tags($cate->description), 55) !!}</td>
                        <!--<td>-->
                        <!--    <div class="badge badge-success badge-shadow">{!! $cate->status !!}</div>-->
                        <!--</td>-->
                        <td>
                            <a href="/dashboard/admin/category/{{  $cate->id  }}/edit" class="btn btn-warning">Edit</a>
                            {!! Form::open(['action' => ['App\Http\Controllers\CategoriesController@destroy',$cate->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
