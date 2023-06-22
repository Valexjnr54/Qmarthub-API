@extends('layouts.admin-layout')
@section('content')
<div class="invoice">
    <div class="invoice-print">
      <div class="row">
        <div class="col-lg-12">
          <div class="invoice-title">
            @if (count($userDetail) >0)
            @foreach ($userDetail as $userDetail)
            <h2>Invoice</h2>
            <div class="invoice-number">Order ID #{{ $userDetail->reference }}</div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-6">
              <address>
                <strong>Billed To:</strong><br>
                Sarah Smith<br>
                6404 Cut Glass Ct,<br>
                Wendell,<br>
                NC, 27591, USA
              </address>
            </div>
            <div class="col-md-6 text-md-right">
              <address>
                <strong>Delivered To:</strong><br>
                {!! $userDetail->last_name.' '.$userDetail->first_name !!}<br>
                {{ $userDetail->location }}
              </address>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <address>
                <strong>Payment Method:</strong><br>
                Visa ending **** 5687<br>
                test@example.com
              </address>
            </div>
            <div class="col-md-6 text-md-right">
              <address>
                <strong>Order Date:</strong><br>
                <?php
                    $date = $userDetail->created_at;
                    echo $date->format('l jS \o\f F Y h:i:s A');
                ?><br><br>
              </address>
            </div>
          </div>
        </div>
      </div>
      @endforeach
      @endif
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="section-title">Order Summary</div>
          <p class="section-lead">All items here cannot be deleted.</p>
          <div class="table-responsive">
            <table class="table table-striped table-hover table-md">
              <tr>
                <th data-width="40">#</th>
                <th>Item</th>
                <th class="text-center">Price</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Totals</th>
              </tr>
              @if (count($orders) > 0)
              <?php $count =0;$subtotal=0;?>
              @foreach ($orders as $order)
              <?php $count++;?>
                <tr>
                    <td>{{ $count }}</td>
                    <td>{{ $order->product_name }}</td>
                    <td class="text-center">₦{{ $order->price }}</td>
                    <td class="text-center">{{ $order->qty }}</td>
                    <td class="text-right">₦{{ $order->price * $order->qty }}</td>

                    <?php $subtotal+=$order->price * $order->qty ?>
                </tr>
              @endforeach

            </table>
          </div>
          <div class="row mt-4">
            <div class="col-lg-8">
              <div class="section-title">Payment Method</div>
              <p class="section-lead">The payment method that we provide is to make it easier for you to pay
                invoices.</p>
              <div class="images">
                <img src="{{ asset('assets/img/cards/visa.png') }}" alt="visa">
                <img src="{{ asset('assets/img/cards/jcb.png') }}" alt="jcb">
                <img src="{{ asset('assets/img/cards/mastercard.png') }}" alt="mastercard">
                <img src="{{ asset('assets/img/cards/paypal.png') }}" alt="paypal">
              </div>
            </div>
            <div class="col-lg-4 text-right">
              <div class="invoice-detail-item">
                <div class="invoice-detail-name">Subtotal</div>
                <div class="invoice-detail-value">₦{{ $subtotal }}</div>
              </div>
              <div class="invoice-detail-item">
                <div class="invoice-detail-name">Shipping</div>
                <div class="invoice-detail-value">₦{{ $userDetail->delivery_fee }}</div>
              </div>
              <hr class="mt-2 mb-2">
              <div class="invoice-detail-item">
                <div class="invoice-detail-name">Total</div>
                <div class="invoice-detail-value invoice-detail-value-lg">₦{{ $subtotal + $userDetail->delivery_fee }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    <hr>
    <div class="text-md-right">
      <div class="float-lg-left mb-lg-0 mb-3">
        <button class="btn btn-primary btn-icon icon-left"><i class="fas fa-credit-card"></i> Process
          Payment</button>
        <button class="btn btn-danger btn-icon icon-left"><i class="fas fa-times"></i> Cancel</button>
      </div>
      <button class="btn btn-warning btn-icon icon-left"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>
@endsection
