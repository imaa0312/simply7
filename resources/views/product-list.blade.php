<?php $page = 'product-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Product List
                @endslot
                @slot('li_1')
                    Manage your products
                @endslot
                @slot('li_2')
                    {{ url('add-product') }}
                @endslot
                @slot('li_3')
                    Add New Product
                @endslot
            @endcomponent

            <!-- /product list -->
            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-input">
                                <a href="javascript:void(0);" class="btn btn-searchset"><i data-feather="search"
                                        class="feather-search"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive product-list">
                        <table class="table" id="myTable">
                            <thead>
                                <tr>
                                    <th class="no-sort">No</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Purchase Price</th>
                                    <th>Sale Price</th>
                                    <!-- <th>Status</th> -->
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /product list -->
        </div>
    </div>

    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
    
    <script>
        $(document).ready(function(){
            $('#images').val("");

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('product-datatables') !!}",
                    type: "get"
                },
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'name', name: 'name'},
                    {data: 'sku', name: 'sku'},
                    {data: 'category', name: 'category'},
                    {data: 'brand', name: 'brand'},
                    {data: 'price_purchase', name: 'price_purchase'},
                    {data: 'price_sale', name: 'price_sale'},
                    // {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', className: "dt-center"}
                ],            
                "bFilter": true,
                "sDom": 'fBtlpi',  
                "ordering": true,
                "language": {
                    search: ' ',
                    sLengthMenu: '_MENU_',
                    searchPlaceholder: "Search",
                    info: "_START_ - _END_ of _TOTAL_ items",
                    paginate: {
                        next: ' <i class=" fa fa-angle-right"></i>',
                        previous: '<i class="fa fa-angle-left"></i> '
                    },
                },
                initComplete: (settings, json)=>{
                    $('.dataTables_filter').appendTo('#tableSearch');
                    $('.dataTables_filter').appendTo('.search-input');
    
                }
            });
            
            $('body').on('click', '.add-prod', function(){
                $('#product_id').val("");
                $('#product_name').val("");
                $('#sku').val("");
                $('#p_price').val('');
                $('#s_price').val('');
                $('#profit').val("");
                $('#qty_alert').val("");
                $('#desc').val('').blur();
                $('#title_modal').html("Create Product");
                $('#images').val("");
                $('#image').val("");
                $('.phone-img').remove();
                $('#addnew').show();

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getCategory") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#category_list').html(data.category);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getSubCategory") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#sub_category_list').html(data.category);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getSsubCategory") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#ssub_category_list').html(data.category);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getSssubCategory") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#sssub_category_list').html(data.category);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getBrand") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#brand_list').html(data.category);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getSize") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#size_list').html(data.category);
                        }
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
                            
                            
                            if(data.images_id == ""){
                                $('#addnew').show()
                            } else {
                                $('#addnew').hide();
                                $('.add-choosen').html('<div class="phone-img"><img src="'+data.images+'" alt="image"><a href="javascript:void(0);"><i class="fa-solid fa-xmark remove-product" data-id="'+data.images_id+'"></i></a></div>');
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
                            $('.add-choosen').append('<div class="phone-img"><img src="'+data.images+'" alt="image"><a href="javascript:void(0);"><i class="fa-solid fa-xmark remove-product" data-id="'+data.id+'"></i></a></div>');
                            $('#addnew').hide();
                            
                            $('#images').val(data.id);
                            $('#images_id').val(data.id);
                            
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
                                title: "Data has been deleted",
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $('#addnew').show();
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
