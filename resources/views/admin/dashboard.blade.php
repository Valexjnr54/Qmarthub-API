@extends('layouts.admin-layout')
@section('content')
    <div class="row ">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">Glocery Orders</h5>
                          <h2 class="mb-3 font-18">{{ count($productOrders) }}</h2>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/1.png" alt="">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15"> Food Orders</h5>
                          <h2 class="mb-3 font-18">{{ count($foodOrders) }}</h2>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/1.png" alt="">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">Bulk Orders</h5>
                          <h2 class="mb-3 font-18">{{ count($bulkOrders) }}</h2>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/1.png" alt="">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
              <div class="card">
                <div class="card-statistic-4">
                  <div class="align-items-center justify-content-between">
                    <div class="row ">
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                        <div class="card-content">
                          <h5 class="font-15">Food Vendors</h5>
                          <h2 class="mb-3 font-18">{{ count($vendors) }}</h2>
                        </div>
                      </div>
                      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                        <div class="banner-img">
                          <img src="assets/img/banner/4.png" alt="">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
              <div class="card">
                  <div class="card-header">
                      <h4>Product Orders</h4>
                  </div>
                  <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-striped" id="table-3">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($productOrders) > 0)
                                    @php
                                        $count = 0;
                                    @endphp
                                    @foreach($productOrders as $productOrder)
                                        @php
                                            $count++;
                                        @endphp
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $productOrder->product_name }}</td>
                                            <td>{{ $productOrder->price }}</td>
                                            <td>{{ $productOrder->qty }}</td>
                                            <td>{{ $productOrder->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                      </div>
                  </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12">
              <div class="card">
                <div class="card-header">
                  <h4>Food Orders</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped" id="table-1">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($foodOrders) > 0)
                                    @php
                                        $count = 0;
                                    @endphp
                                    @foreach($foodOrders as $foodOrder)
                                        @php
                                            $count++;
                                        @endphp
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $foodOrder->product_name }}</td>
                                            <td>{{ $foodOrder->price }}</td>
                                            <td>{{ $foodOrder->qty }}</td>
                                            <td>{{ $foodOrder->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                            @endif
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12">
              <div class="card">
                <div class="card-header">
                  <h4>Bulk Orders</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped" id="table-4">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($bulkOrders) > 0)
                                    @php
                                        $count = 0;
                                    @endphp
                                    @foreach($bulkOrders as $bulkOrder)
                                        @php
                                            $count++;
                                        @endphp
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td>{{ $bulkOrder->product_name }}</td>
                                            <td>{{ $bulkOrder->price }}</td>
                                            <td>{{ $bulkOrder->qty }}</td>
                                            <td>{{ $bulkOrder->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                            @endif
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
@endsection
