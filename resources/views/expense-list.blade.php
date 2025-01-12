<?php $page="expense-list"; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @component('components.breadcrumb')
                @slot('title')
                    Expense List
                @endslot
                @slot('li_1')
                    Manage Your Expenses
                @endslot
                @slot('li_2')
                    Add New Expenses 
                @endslot
            @endcomponent       

        <!-- /product list -->
        <div class="card table-list-card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-input">
                            <a href="" class="btn btn-searchset"><i data-feather="search" class="feather-search"></i></a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="myTable">
                        <thead>
                            <tr>
                                <th class="no-sort">No</th>
                                <th>Category</th>
                                <th>Expense For</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody class="Expense-list-blk">
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
                url: "{!! url('expense-datatables') !!}",
                type: "get"
            },
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                {data: 'category', name: 'category'},
                {data: 'name', name: 'name'},
                {data: 'reference', name: 'reference'},
                {data: 'date', name: 'date'},
                {data: 'amount', name: 'amount'},
                {data: 'description', name: 'description'},
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
        
        $('body').on('click', '.add-expense', function(){
            $('#expense_id').val("");
            $('#nama').val("");
            $('#date').val("");
            $('#amount').val('');
            $('#reference').val("");
            $('#category_list').html("<option>Choose Expense Category</option>");
            $('#desc').val('').blur();
            $('#title_modal').html("Create Expense");
            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("getExpenseCategory") !!}',
                success: function (data) {
                    if (data.status === true) {
                        $('#category_list').html(data.category);
                    }
                },
                fail: function (e) {
                    toastr.error(data.msg);
                }
            });
        });

        $('body').on('click', '.edit-expense', function(){
            $('#title_modal').html("Edit Expense");
            var id = $(this).attr('data-id');
            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("edit-expense") !!}/'+id,
                success: function (data) {
                    if (data.status === true) {
                        $('#expense_id').val(id);
                        $('#nama').val(data.name);
                        $('#date').val(data.date);
                        $('#reference').val(data.reference);
                        $('#amount').val(data.amount);
                        $('#desc').val(data.desc);
                        $('#category_list').html(data.category_list);
                        $("#category option[value="+data.category_id+"]").attr('selected', true); 
                    }
                },
                fail: function (e) {
                    toastr.error(data.msg);
                }
            });
        });
        
        $('body').on('click', '.save-expense', function(){
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
                                url: '{!! url("save-expense") !!}',
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
                                    $('#add-expense').modal('toggle');
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

        $('body').on('click', '.del-expense', function(){
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
                                url: '{!! url("delete-expense") !!}/'+id,
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

        $('body').on('click', '.restore-expense', function(){
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
                                url: '{!! url("restore-expense") !!}/'+id,
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