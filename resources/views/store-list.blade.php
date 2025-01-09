<?php $page = 'store-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Store List
                @endslot
                @slot('li_1')
                    Manage your Store
                @endslot
                @slot('li_2')
                    Add Store
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
                                    <th>Store Name </th>
                                    <th>Address </th>
                                    <th>Manager </th>
                                    <th>Phone</th>
                                    <th>Status</th>
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
            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('store-datatables') !!}",
                    type: "get"
                },
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'name', name: 'name', searchable: true},
                    {data: 'address', name: 'address', searchable: true},
                    {data: 'manager', name: 'manager', searchable: true},
                    {data: 'phone', name: 'phone', searchable: true},
                    {data: 'status', name: 'status', searchable: true},
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

            $('body').on('click', '.add-stores', function(){
                $('#store_id').val("");
                $('#store_name').val("");
                $('#telp').val("");
                $('#address').val("");
                $('#title_modal').html("Create Store");
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getManager") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#manager_list').html(data.manager);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            }) ;

            $('body').on('click', '.edit-store', function(){
                $('#title_modal').html("Edit Store");
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-store") !!}/'+id,
                    success: function (data) {
                        if (data.status === true) {
                            $('#store_id').val(id);
                            $('#store_name').val(data.name);
                            $('#manager_list').html(data.manager_list);
                            $('#telp').val(data.phone);
                            $('#address').val(data.address);
                            $("#manager option[value="+data.manager+"]").attr('selected', true); 
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
            
            $('body').on('click', '.save-store', function(){
                var form = $('#myForm');
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
                                    url: '{!! url("save-store") !!}',
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
                                        $('#add-stores').modal('toggle');
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

            $('body').on('click', '.del-store', function(){
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
                                    url: '{!! url("delete-store") !!}/'+id,
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

            $('body').on('click', '.restore-store', function(){
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
                                    url: '{!! url("restore-store") !!}/'+id,
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
        });
    </script>
@endsection
