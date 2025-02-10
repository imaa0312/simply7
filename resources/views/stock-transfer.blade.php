<?php $page = 'stock-transfer'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Stock Transfer
                @endslot
                @slot('li_1')
                    Manage your stock transfer
                @endslot
                @slot('li_2')
                    Add New
                @endslot
                @slot('li_3')
                    Import Transfer
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
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Ref Number</th>
                                    <th>Input By</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>

    <script>
        $(document).ready(function(){
            $("#ts_date").flatpickr({
                dateFormat: "Y-m-d H:i",
                enableTime: true
            });

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('stock-transfer-datatables') !!}",
                    type: "get"
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'ts_date', name: 'ts_date'},
                    {data: 'tempat_asal', name: 'tempat_asal'},
                    {data: 'tempat_tujuan', name: 'tempat_tujuan'},
                    {data: 'ts_code', name: 'ts_code'},
                    {data: 'user', name: 'user'},
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

            $('#produk').select2({
                ajax: {
                    type: "GET",
                    delay: 250,
                    dataType: 'json',
                    url: '{!! url("ts-prod") !!}/'+$('#asal').val(),
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
                    },
                    cache: true
                },
                dropdownParent: $("#add-units")
            });

            $('body').on('change', '#produk', function(){
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("ts-getMax") !!}/'+$(this).val()+'/'+$('#asal').val(),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        $('#qty').attr('max', data.balance);
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
            })

            $('body').on('click', '#asal', function(){
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("ts-getDest") !!}/'+$(this).val(),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        $('#tujuan').html(data);
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
            })

            $('body').on('click', '.add-ts', function(){
                $('#ts_date').val("");
                $('#tsdate').val("");
                $('#asal').prop('selectedIndex',0);
                $('#tujuan').prop('selectedIndex',0);
                $('#qty').val("");
                $('#desc').val("");
                $('#produk').next(".select2-container").show();
                $('#produk_name').hide();

                $('#ts_date').removeAttr("disabled");
                $('#asal').removeAttr("disabled");
                $('#tujuan').removeAttr("disabled");
                $('#produk_name').removeAttr("disabled");
                $('#qty').removeAttr("disabled");
                $('#desc').removeAttr("disabled");
                $('.save-ts').show();
                $('#tujuan').show();
                $('#tujuan_name').hide();
            });

            $('body').on('click', '.view-ts', function(){
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("ts-edit") !!}/'+$(this).attr('data-id'),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if(data.status === true){
                            $('#ts_id').val(data.id);
                            $('#ts_code').val(data.ts_code);
                            $('#ts_date').val(data.ts_date);
                            $('#asal option[value='+data.asal+']').attr('selected','selected');
                            $('#tujuan_name').val(data.tujuan_name);
                            $('#produk').next(".select2-container").hide();
                            $('#produk_name').show();
                            $('#produk_name').val(data.product_name);
                            $('#qty').val(data.qty);
                            $('#desc').val(data.description);

                            $('#ts_date').attr("disabled", "disabled");
                            $('#asal').attr("disabled", "disabled");
                            $('#tujuan_name').attr("disabled", "disabled");
                            $('#produk_name').attr("disabled", "disabled");
                            $('#qty').attr("disabled", "disabled");
                            $('#desc').attr("disabled", "disabled");
                            $('.save-ts').hide();
                            $('#tujuan').hide();
                            $('#tujuan_name').show();
                        } else {
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

            $('body').on('click', '.save-ts', function(){
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
                                    url: '{!! url("ts-save") !!}',
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
                                        $('#add-units').modal('toggle');
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
        });
    </script>
@endsection
