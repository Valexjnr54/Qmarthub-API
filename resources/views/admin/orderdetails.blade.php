@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
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
                  <th>Buyer Name</th>
                  <th>Transaction Reference</th>
                  <th>E-mail</th>
                  <th>Paying For</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($userDetails) > 0)
                <?php $count =0;?>
                @foreach ($userDetails as $userDetail)
                @if($userDetail->payingfor == 'Product')
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $userDetail->last_name.' '.$userDetail->first_name !!}</td>
                  <td>{!! $userDetail->reference !!}</td>
                  <td>{!! $userDetail->email !!}</td>
                  <td>{!! $userDetail->payingfor !!}</td>
                  <td>
                    @if ($userDetail->status == 0)
                        <div class="badge badge-warning badge-shadow">Pending Payment</div>
                    @elseif ($userDetail->status = 1)
                        <div class="badge badge-success badge-shadow">Payment Completed</div>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.order',['id'=> $userDetail->user_id ,'reference'=> $userDetail->reference ]) }}" class="btn btn-info">View Order</a>
                            {{-- {!! Form::open(['action' => ['BrandsController@destroy',$userDetail->id], 'method' => 'POST','class' => 'pull-right']) !!}
                            {{ Form::submit('Delete',['class' => 'btn btn-danger']) }}
                            {!! Form::hidden('_method','DELETE') !!}
                        {!! Form::close() !!} --}}
                  </td>
                </tr>
                @endif
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