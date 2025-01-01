<?php $page = 'users'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    User List
                @endslot
                @slot('li_1')
                    Manage Your Users
                @endslot
                @slot('li_2')
                    Add New User
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
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
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
        </div>
    </div>

    <script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>

    <script>
        $(document).ready(function(){
            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('users-datatables') !!}",
                    type: "get"
                },
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'name', name: 'name'},
                    {data: 'username', name: 'username'},
                    {data: 'role_name', name: 'rorole_namele'},
                    {data: 'phone', name: 'phone'},
                    {data: 'status', name: 'status'},
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
            
            $('body').on('click', '.add-users', function(){
                $('#users_id').val("");
                $('#user_name').val("");
                $('#uname').val("");
                $('#password').val("");
                $('#password_confirmation').val("");
                $('#telp').val("");
                $('#date').val("");
                $('#address').val('').blur();
                $('#title_modal').html("Create User");
                $('#conf').show();

                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("getRole") !!}',
                    success: function (data) {
                        if (data.status === true) {
                            $('#role_list').html(data.roles);
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });

            $('body').on('click', '.edit-users', function(){
                $('#title_modal').html("Edit User");
                var id = $(this).attr('data-id');
                $.ajax({
                    type : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-users") !!}/'+id,
                    success: function (data) {
                        if (data.status === true) {
                            $('#users_id').val(id);
                            $('#user_name').val(data.name);
                            $('#uname').val(data.username);
                            $('#telp').val(data.phone);
                            $('#address').val(data.address);
                            $('#role_list').html(data.role);
                            $('#date').val(data.birthdate);
                            $('#conf').hide();
                            $("#roles option[value="+data.role_id+"]").attr('selected', true); 
                        }
                    },
                    fail: function (e) {
                        toastr.error(data.msg);
                    }
                });
            });
            
            $('body').on('click', '.save-users', function(){
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
                                    url: '{!! url("save-users") !!}',
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
                                            $('#add-users').modal('toggle');
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

            $('body').on('click', '.del-users', function(){
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
                                    url: '{!! url("delete-users") !!}/'+id,
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

            $('body').on('click', '.restore-users', function(){
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
                                    url: '{!! url("restore-users") !!}/'+id,
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
