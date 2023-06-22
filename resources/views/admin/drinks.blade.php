@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <a href="/dashboard/admin/drink/create" class="btn btn-outline-primary">Add New Product</a><br><br>
      <div class="card">
        <div class="card-header">
          <h4>Drinks</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped" id="table-1">
              <thead>
                <tr>
                  <th class="text-center">
                    #
                  </th>
                  <th>drink Name</th>
                  <th>Price</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($drinks) > 0)
                <?php $count =0;?>
                @foreach ($drinks as $drink)
                <?php $count++;?>
                <tr>
                    <td><?= $count?></td>
                    <td>{!! $drink->name !!}</td>
                    <td>{!! $drink->price !!}</td>
                    <td>
                        <a href="/dashboard/admin/drink/{{  $drink->id  }}/edit" class="btn btn-warning">Edit</a>
                        {!! Form::open(['action' => ['App\Http\Controllers\DrinksController@destroy',$drink->id], 'method' => 'POST','class' => 'pull-right']) !!}
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
