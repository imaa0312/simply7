<?php $page = 'purchase-returns'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Purchase Receive
                @endslot
                @slot('li_1')
                    Manage your Receive
                @endslot
                @slot('li_2')
                    Add New Purchase Receive
                @endslot
            @endcomponent

            <!-- /product list -->
            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-input">
                                <a href="" class="btn btn-searchset"><i data-feather="search"
                                        class="feather-search"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table" id="myTable">
                            <thead>
                                <tr>
                                    <th class="no-sort">No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Reference</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Received By</th>
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
            $("#date").flatpickr({
                dateFormat: "Y-m-d H:i",
                enableTime: true
            });

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('pr-datatables') !!}",
                    type: "get"
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'pr_date', name: 'pr_date'},
                    {data: 'supp_name', name: 'supp_name'},
                    {data: 'refno_po', name: 'refno_po'},
                    {data: 'pr_code', name: 'pr_code'},
                    {data: 'status', name: 'status'},
                    {data: 'received_by', name: 'received_by'},
                    {data: 'action', name: 'action'}
                ],            
                "bFilter": true,
                "sDom": 'fBtlpi',  
                "ordering": false,
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

                },
                "autoWidth": false
            });

            $('#refno').select2({
                ajax: {
                    type: "GET",
                    delay: 250,
                    dataType: 'json',
                    url: '{!! url("getRef") !!}',
                    data: function (param) {
                        return {
                            q: param.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.refno,
                                    id: item.id
                                }
                            })
                        };
                    }
                },
                dropdownParent: $("#add-received")
            });

            $('body').on('click', '.btn-add-pr', function(){
                $('#barcode').val("");
                $('#pr_code').val("");
                $('#pr_id').val(0);
                $('.view').show();
                $('#pr_date').hide();
                $('.prdate').show();
                $('#refno_po').hide();
                $('#refno').select().next().show();
            });

            $('body').on('keypress', '#barcode', function (e) {
                if($('#refno') != null){
                    var key = e.which;
                    if(key == 13)  // the enter key code
                    {
                        $.ajax({
                            method : "GET",
                            dataType: 'json',
                            url: '{!! url("pr-barcode") !!}/'+$('#refno').val()+'/'+$(this).val(),
                            cache: false,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.status === true) {
                                    $('#pr_id').val(data.pr_id);
                                    $('#refno_po').show();
                                    $("#refno").select2().next().hide();
                                    
                                    $('#myTable2').DataTable().clear();
                                    $('#myTable2').DataTable().destroy();
                                    $('#myTable2').DataTable({
                                        processing: true,
                                        serverSide: true,
                                        ajax: {
                                            url: "{!! url('pr-product-datatables') !!}/"+$('#pr_id').val(),
                                            type: "get"
                                        },
                                        columns: [
                                            {data: 'product', name: 'product'},
                                            {data: 'qty', name: 'qty'},
                                            {data: 'status_produk', name: 'status_produk'}
                                        ],            
                                        "bFilter": false,
                                        "sDom": 'fBtlpi',  
                                        "ordering": false,
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
                            
                                        },
                                        "autoWidth": false
                                    });

                                    $('#barcode').val("");
                                }  else {
                                    Swal.fire({
                                        position: "top-end",
                                        toast: true,
                                        title: data.msg,
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
                    }
                } else {
                    Swal.fire({
                        position: "top-end",
                        toast: true,
                        title: "Please select the Reference No first",
                        icon: "error",
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });  

            $('#add-received').on("hide.bs.modal", function() {
                $('#myTable').DataTable().ajax.reload();
            });
            
            $('body').on('click', '.save-pr', function(){
                if($('#date') != null){
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
                                        url: '{!! url("save-pr") !!}',
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
                                            $('#add-received').modal('toggle');
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
                } else {
                    Swal.fire({
                        position: "top-end",
                        toast: true,
                        title: "Please fill the Receive Date field first",
                        icon: "error",
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            });

            $('body').on('click', '.edit-pr', function () {
                $('#pr_id').val($(this).attr('data-id'));
                $('.view').show();
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-pr") !!}/'+$('#pr_id').val(),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            $('#myTable2').DataTable().clear();
                            $('#myTable2').DataTable().destroy();
                            $('#myTable2').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: "{!! url('pr-product-datatables') !!}/"+$('#pr_id').val(),
                                    type: "get"
                                },
                                columns: [
                                    {data: 'product', name: 'product'},
                                    {data: 'qty', name: 'qty'},
                                    {data: 'status_produk', name: 'status_produk'}
                                ],            
                                "bFilter": false,
                                "sDom": 'fBtlpi',  
                                "ordering": false,
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
                    
                                },
                                "autoWidth": false
                            });

                            $('#date').val(data.po_date);
                            $("#refno").select2().next().hide();
                            $('#refno_po').show();
                            $("#refno_po").val(data.refno_po);
                            $("#pr_code").val(data.pr_code);
                            $('.prdate').hide();
                            $('#pr_date').show();
                            $('#pr_code').val(data.pr_code);
                        }  else {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                title: data.msg,
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

            $('body').on('click', '.view-pr', function () {
                $('#pr_id').val($(this).attr('data-id'));
                $('.view').hide();
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-pr") !!}/'+$('#pr_id').val(),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            $('#myTable2').DataTable().clear();
                            $('#myTable2').DataTable().destroy();
                            $('#myTable2').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: "{!! url('pr-product-datatables') !!}/"+$('#pr_id').val(),
                                    type: "get"
                                },
                                columns: [
                                    {data: 'product', name: 'product'},
                                    {data: 'qty', name: 'qty'},
                                    {data: 'status_produk', name: 'status_produk'}
                                ],            
                                "bFilter": false,
                                "sDom": 'fBtlpi',  
                                "ordering": false,
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
                    
                                },
                                "autoWidth": false
                            });

                            $('#pr_date').val(data.pr_date);
                            $('#pr_date').show();
                            $('.prdate').hide();
                            $("#refno").select2().next().hide();
                            $('#refno_po').show();
                            $("#refno_po").val(data.refno_po);
                            $("#pr_code").val(data.pr_code);
                        }  else {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                title: data.msg,
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
