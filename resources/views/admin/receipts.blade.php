@extends('layouts.admin-layout')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
        <div class="card-header">
          <h4>Receipts </h4>
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
                  <th>Phone Number</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if (count($receipts) > 0)
                <?php $count =0;?>
                @foreach ($receipts as $receipt)
                <?php $count++;?>
                <tr>
                    <td>
                        <?= $count?>
                    </td>
                  <td>{!! $receipt->last_name.' '.$receipt->first_name !!}</td>
                  <td>{!! $receipt->reference !!}</td>
                  <td>{!! $receipt->email !!}</td>
                  <td>{{  $receipt->phone_number }}</td>
                  <td>
                    <button class='btn btn-info viewdetails' data-id='{{ $receipt->id }}' >View Details</button>
                    <a href="{{ route('admin.delete-receipt',['id' => $receipt->id]) }}" class="btn btn-danger">Delete</a>
                    {{-- <a href="javascript:void();" data-id="{!! $receipt->reference !!}" class="btn btn-info viewReceipt">View Receipt</a> --}}
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
@section('extra-js')
<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="empModal"  tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
aria-hidden="true">
    <div class="modal-dialog modal-lg">

       <!-- Modal content-->
       <div class="modal-content">
          <div class="modal-header">
             <h4 class="modal-title">Receipt Info</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          </div>
          <div class="modal-body">
              <table class="w-100" id="tblempinfo">
                 <tbody></tbody>
              </table>
          </div>
          <div class="modal-footer">
              <a href="{{ route('admin.confirm-receipt',['id' => $receipt->id]) }}" class="btn btn-success">Confirm Payment</a>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
          </div>
       </div>
    </div>
 </div>
<!-- /.modal -->
<!-- Script -->
<script type='text/javascript'>
    $(document).ready(function(){

       $('#table-1').on('click','.viewdetails',function(){
           var empid = $(this).attr('data-id');

           if(empid > 0){

              // AJAX request
              var url = "{{ route('dynamicModal',[':empid']) }}";
              url = url.replace(':empid',empid);

              // Empty modal data
              $('#tblempinfo tbody').empty();

              $.ajax({
                  url: url,
                  dataType: 'json',
                  success: function(response){

                      // Add employee details
                      $('#tblempinfo tbody').html(response.html);

                      // Display Modal
                      $('#empModal').modal('show');
                  }
              });
           }
       });

    });
    </script>
@endsection
