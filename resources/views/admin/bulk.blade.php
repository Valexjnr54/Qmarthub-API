@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/bulk/create" class="btn btn-outline-primary">Add New Bulk Item</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Bulk </h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>Bulk Name</th>
                  <th>Price</th>
                  <th>Discount</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($bulks) > 0)
                <?php $count =0;?>
                @foreach ($bulks as $bulk)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $bulk->name !!}</td>
                  <td>{!! $bulk->price !!}</td>
                  <td>{!! $bulk->discount !!}</td>
                  <td>
                    <a href="/dashboard/admin/bulk/{{  $bulk->id  }}/edit" class="btn btn-warning">Edit</a>
                            {!! Form::open(['action' => ['App\Http\Controllers\BulkController@destroy',$bulk->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
