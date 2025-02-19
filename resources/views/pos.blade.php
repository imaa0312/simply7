<?php $page = 'pos'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper pos-pg-wrapper ms-0">
        <div class="content pos-design p-0">
            <div class="btn-row d-sm-flex align-items-center">
                <a href="javascript:void(0);" class="btn btn-secondary mb-xs-3" data-bs-toggle="modal"
                    data-bs-target="#orders"><span class="me-1 d-flex align-items-center"><i data-feather="shopping-cart"
                            class="feather-16"></i></span>View Orders</a>
                <a href="javascript:void(0);" class="btn btn-info"><span class="me-1 d-flex align-items-center"><i
                            data-feather="rotate-cw" class="feather-16"></i></span>Reset</a>
                <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recents"><span
                        class="me-1 d-flex align-items-center"><i data-feather="refresh-ccw"
                            class="feather-16"></i></span>Transaction</a>
            </div>

            <div class="row align-items-start pos-wrapper">
                <div class="col-md-12 col-lg-6">
                    <div class="pos-categories tabs_wrapper">
                        <h5>Categories</h5>
                        <p>Select From Below Categories</p>
                        <ul class="tabs owl-carousel pos-category">
                            <li id="all" class="btn_cat" data-id="0">
                                <h6><a href="javascript:void(0);">All</a></h6>
                            </li>
                            @foreach($data['category'] as $cat)
                            <li id="{{{ $cat->code }}}" class="btn_cat" data-id="{{{ $cat->id }}}">
                                <h6><a href="javascript:void(0);">{{{ $cat->name }}}</a></h6>
                            </li>
                            @endforeach
                        </ul>
                        <div class="pos-products">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-3">Products</h5>
                            </div>
                            <div class="tabs_container">
                                <div class="tab_content tab-all active" data-tab="all">
                                </div>
                                @foreach($data['category'] as $cat)
                                <div class="tab_content product_div" data-tab="{{{ $cat->code }}}">
                                    <div class="row ">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6 ps-0">
                    <aside class="product-order-list">
                        <div class="head d-flex align-items-center justify-content-between w-100">
                            <div class="">
                                <h5>Order List</h5>
                                <span>Transaction ID : #65565</span>
                            </div>
                        </div>
                        <form id="formKu" method="POST">
                        @csrf
                        <input type="hidden" id="cart_id">
                        <div class="customer-info block-section">
                            <h6>Customer Information</h6>
                            <div class="input-block d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <select class="select2 form-control" id="cust">
                                        <option value="0">Walk in Customer</option>
                                    </select>
                                </div>
                                <a href="#" class="btn btn-primary btn-icon" data-bs-toggle="modal"
                                    data-bs-target="#create"><i data-feather="user-plus" class="feather-16"></i></a>
                            </div>
                            <div class="input-block">
                                <select class="select2 form-control" id="prod-list">
                                    <option value="0">Search Products</option>
                                </select>
                            </div>
                        </div>

                        <div class="product-added block-section">
                            <div class="head-text d-flex align-items-center justify-content-between">
                                <h6 class="d-flex align-items-center mb-0">Product Added<span class="count" id="count">0</span></h6>
                            </div>
                            <div class="product-wrap" id="productadded">
                            </div>
                        </div>
                        <div class="block-section">
                            <!--<div class="selling-info">
                                <div class="row">
                                    <div class="col-12 col-sm-4">
                                        <div class="input-block">
                                            <label>Discount</label>
                                            <select class="select">
                                                <option>10%</option>
                                                <option>10%</option>
                                                <option>15%</option>
                                                <option>20%</option>
                                                <option>25%</option>
                                                <option>30%</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                            <div class="order-total">
                                <table class="table table-responsive table-borderless">
                                    <tr>
                                        <td>Sub Total</td>
                                        <td class="text-end" id="subtot">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td>Tax (10%)</td>
                                        <td class="text-end" id="tax">Rp 0</td>
                                    </tr>
                                    <!--<tr>
                                        <td class="danger">Discount (10%)</td>
                                        <td class="danger text-end">$15.21</td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-end"></td>
                                    </tr>-->
                                </table>
                            </div>
                        </div>

                        <div class="block-section payment-method">
                            <h6>Payment Method</h6>
                            <div class="row d-flex align-items-center justify-content-center methods">
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover" data-id="cash">
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#payment-mod" data-id="cash" class="payment-method">
                                            <img src="{{ URL::asset('/build/img/icons/cash-pay.svg')}}"
                                                alt="Payment Method">
                                            <span>Cash</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover" data-id="edc">
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#payment-mod" data-id="card" class="payment-method">
                                            <img src="{{ URL::asset('/build/img/icons/credit-card.svg')}}"
                                                alt="Payment Method">
                                            <span>Debit Card</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover" data-id="qr">
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#payment-mod" data-id="qr" class="payment-method">
                                            <img src="{{ URL::asset('/build/img/icons/qr-scan.svg')}}" alt="Payment Method">
                                            <span>Scan QR</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid btn-block">
                            <a class="btn btn-secondary" href="javascript:void(0);" id="total">
                                Grand Total : Rp 0
                            </a>
                        </div>
                        <!--<div class="btn-row d-sm-flex align-items-center justify-content-between">
                            <a href="javascript:void(0);" class="btn btn-info btn-icon flex-fill"
                                data-bs-toggle="modal" data-bs-target="#hold-order"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="pause"
                                        class="feather-16"></i></span>Hold</a>
                            <a href="javascript:void(0);" class="btn btn-danger btn-icon flex-fill" id="void"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="trash-2"
                                        class="feather-16"></i></span>Void</a>
                            <a href="javascript:void(0);" class="btn btn-success btn-icon flex-fill"
                                data-bs-toggle="modal" data-bs-target="#payment-completed"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="credit-card"
                                        class="feather-16"></i></span>Payment</a>
                        </div>-->
                        </form>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
    
    <script>
        $(document).ready(function(){
            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("getProduct") !!}/0',
                success: function (data) {
                    $('.tab-all').html(data.grid);
                },
                fail: function (e) {
                    toastr.error(data.msg);
                }
            });

            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("loadcart") !!}/'+0,
                success: function (data) {
                    $('#productadded').html(data);
                },
                fail: function (e) {
                    toastr.error(data.msg);
                }
            });

            $('body').on('click', '.btn_cat', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getProduct") !!}/'+id,
                    success: function (data) {
                        $('.product_div').html(data.grid);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });

            $('body').on('click', '.payment-method', function(event){
                event.stopImmediatePropagation();
                var method = $(this).attr('data-id');
                alert(method);

                if(method == 'cash'){
                    $('.txtbox').show();
                    $('#label-payment').html('Cash');
                    $('#label-change').show();
                    $('#label-change').html('Change');
                    $('#change-money').html('Change');
                } else if(method == 'card'){
                    $('.txtbox').show();
                    $('#label-payment').html('Card Number');
                    $('#label-change').hide();
                    $('#change-money').hide();
                } else {
                    $('.txtbox').hide();
                }
            });

            $('body').on('click', '.prod', function(){
                var id = $(this).attr('data-id');

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("addtocart") !!}/'+id,
                    success: function (data) {
                        $('#productadded').html(data.cart);
                        $('#cart_id').val(data.id);
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('click', '.dec', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("pos-qty") !!}/'+id+'/'+0,
                    success: function (data) {
                        $('#productadded').html(data.cart);
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('click', '.inc', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("pos-qty") !!}/'+id+'/'+1,
                    success: function (data) {
                        $('#productadded').html(data.cart);
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('change', '.qty', function(){
                var id = $(this).attr('data-id');
                var qty = $(this).val();
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("fill-qty") !!}/'+id+'/'+qty,
                    success: function (data) {
                        $('#productadded').html(data.cart);
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('click', '.del', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("pos-del") !!}/'+id,
                    success: function (data) {
                        $('#productadded').html(data.cart);
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('click', '#void', function(){
                var id = $('#cart_id').val();
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("pos-void") !!}/'+id,
                    success: function (data) {  
                        $('#productadded').html("");
                        $('#cart_id').val("");
                        $('#count').html(data.count);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });

            $('#cust').select2({
                ajax: {
                    type: "GET",
                    dataType: 'json',
                    url: '{!! url("pos-cust") !!}',
                    data: function (param) {
                        return {
                            q: param.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    }
                }
            });

            $('#prod-list').select2({
                ajax: {
                    type: "GET",
                    dataType: 'json',
                    url: '{!! url("pos-prod") !!}',
                    data: function (param) {
                        return {
                            q: param.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    // text: item.brand+' - '+item.name+' - '+item.size+' - '.item.sku,
                                    text: item.brand+' - '+item.name+' - '+item.size,
                                    id: item.id
                                }
                            })
                        };
                    }
                }
            });
        });
    </script>
@endsection