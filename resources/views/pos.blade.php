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
                                <div class="tab_content active" data-tab="all">
                                    <div class="row">
                                        @foreach($data['all_product'] as $all)
                                        <div class="col-sm-2 col-md-6 col-lg-3 col-xl-3 prod" id="p{{{ $all['id'] }}}" data-id={{{ $all['id'] }}}>
                                            <div class="product-info default-cover card">
                                                <a href="javascript:void(0);" class="img-bg">
                                                    <img src="{{ URL::asset('/product_images') }}/{{{ $all['image'] }}}"
                                                        alt="Products">
                                                    <span><i data-feather="check" class="feather-16"></i></span>
                                                </a>
                                                <h6 class="product-name"><a href="javascript:void(0);">{{{ $all['name'] }}}</a>
                                                </h6>
                                                <div class="d-flex align-items-center justify-content-between price">
                                                    <p>Rp {{{ $all['price_sale'] }}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
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
                            <div class="">
                                <a class="confirm-text" href="javascript:void(0);"><i data-feather="trash-2"
                                        class="feather-16 text-danger"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="more-vertical"
                                        class="feather-16"></i></a>
                            </div>
                        </div>
                        <div class="customer-info block-section">
                            <h6>Customer Information</h6>
                            <div class="input-block d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <select class="select">
                                        <option>Walk in Customer</option>
                                        <option>John</option>
                                        <option>Smith</option>
                                        <option>Ana</option>
                                        <option>Elza</option>
                                    </select>
                                </div>
                                <a href="#" class="btn btn-primary btn-icon" data-bs-toggle="modal"
                                    data-bs-target="#create"><i data-feather="user-plus" class="feather-16"></i></a>
                            </div>
                            <div class="input-block">
                                <select class="select">
                                    <option>Search Products</option>
                                    <option>IPhone 14 64GB</option>
                                    <option>MacBook Pro</option>
                                    <option>Rolex Tribute V3</option>
                                    <option>Red Nike Angelo</option>
                                    <option>Airpod 2</option>
                                    <option>Oldest</option>
                                </select>
                            </div>
                        </div>

                        <div class="product-added block-section">
                            <div class="head-text d-flex align-items-center justify-content-between">
                                <h6 class="d-flex align-items-center mb-0">Product Added<span class="count">2</span></h6>
                                <a href="javascript:void(0);" class="d-flex align-items-center text-danger"><span
                                        class="me-1"><i data-feather="x" class="feather-16"></i></span>Clear all</a>
                            </div>
                            <div class="product-wrap" id="productadded">
                                <div class="product-list d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center product-info" data-bs-toggle="modal"
                                        data-bs-target="#products">
                                        <a href="javascript:void(0);" class="img-bg">
                                            <img src="{{ URL::asset('/build/img/products/pos-product-16.png') }}"
                                                alt="Products">
                                        </a>
                                        <div class="info">
                                            <span>PT0005</span>
                                            <h6><a href="javascript:void(0);">Red Nike Laser</a></h6>
                                            <p>$2000</p>
                                        </div>
                                    </div>
                                    <div class="qty-item text-center">
                                        <a href="javascript:void(0);"
                                            class="dec d-flex justify-content-center align-items-center"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i
                                                data-feather="minus-circle" class="feather-14"></i></a>
                                        <input type="text" class="form-control text-center" name="qty"
                                            value="4">
                                        <a href="javascript:void(0);"
                                            class="inc d-flex justify-content-center align-items-center"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i
                                                data-feather="plus-circle" class="feather-14"></i></a>
                                    </div>
                                    <div class="d-flex align-items-center action">
                                        <a class="btn-icon edit-icon me-2" href="#" data-bs-toggle="modal"
                                            data-bs-target="#edit-product">
                                            <i data-feather="edit" class="feather-14"></i>
                                        </a>
                                        <a class="btn-icon delete-icon confirm-text" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-14"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="block-section">
                            <div class="selling-info">
                                <div class="row">
                                    <div class="col-12 col-sm-4">
                                        <div class="input-block">
                                            <label>Order Tax</label>
                                            <select class="select">
                                                <option>GST 5%</option>
                                                <option>GST 10%</option>
                                                <option>GST 15%</option>
                                                <option>GST 20%</option>
                                                <option>GST 25%</option>
                                                <option>GST 30%</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="input-block">
                                            <label>Shipping</label>
                                            <select class="select">
                                                <option>15</option>
                                                <option>20</option>
                                                <option>25</option>
                                                <option>30</option>
                                            </select>
                                        </div>
                                    </div>
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
                            </div>
                            <div class="order-total">
                                <table class="table table-responsive table-borderless">
                                    <tr>
                                        <td>Sub Total</td>
                                        <td class="text-end">$60,454</td>
                                    </tr>
                                    <tr>
                                        <td>Tax (GST 5%)</td>
                                        <td class="text-end">$40.21</td>
                                    </tr>
                                    <tr>
                                        <td>Shipping</td>
                                        <td class="text-end">$40.21</td>
                                    </tr>
                                    <tr>
                                        <td>Sub Total</td>
                                        <td class="text-end">$60,454</td>
                                    </tr>
                                    <tr>
                                        <td class="danger">Discount (10%)</td>
                                        <td class="danger text-end">$15.21</td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-end">$64,024.5</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="block-section payment-method">
                            <h6>Payment Method</h6>
                            <div class="row d-flex align-items-center justify-content-center methods">
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover">
                                        <a href="javascript:void(0);">
                                            <img src="{{ URL::asset('/build/img/icons/cash-pay.svg')}}"
                                                alt="Payment Method">
                                            <span>Cash</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover">
                                        <a href="javascript:void(0);">
                                            <img src="{{ URL::asset('/build/img/icons/credit-card.svg')}}"
                                                alt="Payment Method">
                                            <span>Debit Card</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 item">
                                    <div class="default-cover">
                                        <a href="javascript:void(0);">
                                            <img src="{{ URL::asset('/build/img/icons/qr-scan.svg')}}" alt="Payment Method">
                                            <span>Scan</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid btn-block">
                            <a class="btn btn-secondary" href="javascript:void(0);">
                                Grand Total : $64,024.5
                            </a>
                        </div>
                        <div class="btn-row d-sm-flex align-items-center justify-content-between">
                            <a href="javascript:void(0);" class="btn btn-info btn-icon flex-fill"
                                data-bs-toggle="modal" data-bs-target="#hold-order"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="pause"
                                        class="feather-16"></i></span>Hold</a>
                            <a href="javascript:void(0);" class="btn btn-danger btn-icon flex-fill"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="trash-2"
                                        class="feather-16"></i></span>Void</a>
                            <a href="javascript:void(0);" class="btn btn-success btn-icon flex-fill"
                                data-bs-toggle="modal" data-bs-target="#payment-completed"><span
                                    class="me-1 d-flex align-items-center"><i data-feather="credit-card"
                                        class="feather-16"></i></span>Payment</a>
                        </div>

                    </aside>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
    
    <script>
        $(document).ready(function(){
            $('body').on('click', '.btn_cat', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getProduct") !!}/'+id,
                    success: function (data) {
                        $('.product_div').html(data);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });

            $('body').on('click', '.prod', function(){
                var id = $(this).attr('data-id');

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("addtocart") !!}/'+id,
                    success: function (data) {
                        $('#productadded').append(data);
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
    
            $('body').on('click', '.edit-product', function(){
                $('#title_modal').html("Edit Product");
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-product") !!}/'+id,
                    success: function (data) {
                        if (data.status === true) {
                            $('#product_id').val(data.id);
                            $('#product_name').val(data.name);
                            $('#sku').val(data.sku);
                            $('#p_price').val(data.price_purchase);
                            $('#s_price').val(data.price_sale);
                            $('#profit').val(data.profit_percent);
                            $('#qty_alert').val(data.stok_minimal);
                            $('#category_list').html(data.kategori_list);
                            $('#sub_category_list').html(data.sub_kategori_list);
                            $('#ssub_category_list').html(data.ssub_kategori_list);
                            $('#sssub_category_list').html(data.sssub_kategori_list);
                            $('#brand_list').html(data.brand_list);
                            $('#size_list').html(data.size_list);
                            $('#desc').html(data.description);
                            $("#category option[value="+data.kategori_id+"]").attr('selected', true); 
                            $("#subcategory option[value="+data.sub_kategori_id+"]").attr('selected', true); 
                            $("#ssubcategory option[value="+data.ssub_kategori_id+"]").attr('selected', true); 
                            $("#sssubcategory option[value="+data.sssub_kategori_id+"]").attr('selected', true); 
                            $("#brand option[value="+data.brand_id+"]").attr('selected', true); 
                            $("#size option[value="+data.size_id+"]").attr('selected', true); 
                            $('#images').val(data.images_id);
                            $('#images_id').val(data.images_id);
                            $('.add-choosen').html('<div class="phone-img"><img src="'+data.images+'" alt="image"><a href="javascript:void(0);"><i class="fa-solid fa-xmark remove-product" data-id="'+data.images_id+'"></i></a></div>');
                            
                            if(data.images_id == ""){
                                $('.image-upload').show();
                            } else {
                                $('.image-upload').hide();
                            }
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
            
            $('body').on('click', '.save-product', function(){
                var form = $('#formKu');
                var formdata = new FormData(form[0]);
    
                bootbox.dialog({
                    message: "Are you sure want to save this data ?",
                    title: "Save Confirmation",
                    buttons: {
                        success: {
                            label: "Yes",
                            className: "btn-success btn-flat",
                            callback: function () {
                                $.ajax({
                                    method : "POST",
                                    dataType: 'json',
                                    url: '{!! url("save-product") !!}',
                                    data: formdata,
                                    cache: false,
                                    processData: false,
                                    contentType: false,
                                    success: function (data) {
                                        if (data.status === true) {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                icon: "success",
                                                title: "Data has been saved",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        } else {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                title: "Something wen't Wrong",
                                                icon: "error",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        }
                                        $('#add-products').modal('toggle');
                                        $('#myTable').DataTable().ajax.reload();
                                    },
                                    fail: function (e) {
                                        Swal.fire({
                                            position: "top-end",
                                            toast: true,
                                            title: "Something wen't Wrong",
                                            icon: "error",
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                    }
                                });
                            }
                        },
                        danger: {
                            label: "Cancel",
                            className: "btn-danger btn-flat"
                        }
                    }
                });
            });
    
            $('body').on('click', '.del-product', function(){
                var id = $(this).attr('data-id');
    
                bootbox.dialog({
                    message: "Are you sure want to delete this data ?",
                    title: "Confirmation",
                    buttons: {
                        success: {
                            label: "Yes",
                            className: "btn-success btn-flat",
                            callback: function () {
                                $.ajax({
                                    method : "GET",
                                    dataType: 'json',
                                    url: '{!! url("delete-product") !!}/'+id,
                                    success: function (data) {
                                        if (data.status === true) {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                icon: "success",
                                                title: "Data has been deleted",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        } else {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                title: "Something wen't Wrong",
                                                icon: "error",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        }
                                        
                                        $('#myTable').DataTable().ajax.reload();
                                    },
                                    fail: function (e) {
                                        Swal.fire({
                                            position: "top-end",
                                            toast: true,
                                            title: "Something wen't Wrong",
                                            icon: "error",
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                    }
                                });
                            }
                        },
                        danger: {
                            label: "Cancel",
                            className: "btn-danger btn-flat"
                        }
                    }
                });
            });
    
            $('body').on('click', '.restore-product', function(){
                var id = $(this).attr('data-id');
                bootbox.dialog({
                    message: "Are you sure want to restore this data ?",
                    title: "Confirmation",
                    buttons: {
                        success: {
                            label: "Yes",
                            className: "btn-success btn-flat",
                            callback: function () {
                                $.ajax({
                                    method : "GET",
                                    dataType: 'json',
                                    url: '{!! url("restore-product") !!}/'+id,
                                    success: function (data) {
                                        if (data.status === true) {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                icon: "success",
                                                title: "Data has been restored",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        } else {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                title: "Something wen't Wrong",
                                                icon: "error",
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        }
                                        
                                        $('#myTable').DataTable().ajax.reload();
                                    },
                                    fail: function (e) {
                                        Swal.fire({
                                            position: "top-end",
                                            toast: true,
                                            title: "Something wen't Wrong",
                                            icon: "error",
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                    }
                                });
                            }
                        },
                        danger: {
                            label: "Cancel",
                            className: "btn-danger btn-flat"
                        }
                    }
                });
            });

            $('body').on('change', '#image', function(){
                var formData = new FormData();
                formData.append('image', $(this)[0].files[0]);
                formData.append('product_id', $('#product_id').val());
                var array_images = $('#images').val();

                $.ajax({
                    method : "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    dataType: 'json',
                    url: '{!! url("save-product-images") !!}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                icon: "success",
                                title: "Data has been saved",
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $('.add-choosen').html('<div class="phone-img"><img src="'+data.images+'" alt="image"><a href="javascript:void(0);"><i class="fa-solid fa-xmark remove-product" data-id="'+data.id+'"></i></a></div>');
                            
                            $('#images').val(data.id);
                            $('.image-upload').hide();
                            
                        } else {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                title: "Something wen't Wrong",
                                icon: "error",
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    fail: function (e) {
                        Swal.fire({
                            position: "top-end",
                            toast: true,
                            title: "Something wen't Wrong",
                            icon: "error",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                });
            });

            $('body').on('click', '.remove-product', function(){
                var id = $(this).attr('data-id');
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("del-image-product") !!}/'+id,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                icon: "success",
                                title: "Data has been saved",
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                title: "Something wen't Wrong",
                                icon: "error",
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    fail: function (e) {
                        Swal.fire({
                            position: "top-end",
                            toast: true,
                            title: "Something wen't Wrong",
                            icon: "error",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                });
            });
        });
    </script>
@endsection
