<?php $page = 'category-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Sub Category Lists
                @endslot
                @slot('li_1')
                    Manage your sub categories
                @endslot
                @slot('li_2')
                    Add Sub Category
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
                                    <!--<th>Category</th>-->
                                    <th>Name</th>
                                    <th>Code</th>
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
                url: "{!! url('subcategory-datatables') !!}",
                type: "get"
            },
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                // {data: 'kategori', name: 'kategori', searchable: true},
                {data: 'name', name: 'name', searchable: true},
                {data: 'code', name: 'code', searchable: true},
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
        
        $('body').on('click', '.add-subcat', function(){
            $('#cat_id').val("");
            $('#cat_name').val("");
            $('#code').val("");
            $('#title_modal').html("Create Sub Category");
            // $.ajax({
            //     type : "GET",
            //     dataType: 'json',
            //     url: '{!! url("getCategory") !!}',
            //     success: function (data) {
            //         if (data.status === true) {
            //             $('#category').html(data.category);
            //         }
            //     },
            //     fail: function (e) {
            //         toastr.error(data.msg);
            //     }
            // });
        });

        $('body').on('click', '.edit-cat', function(){
            $('#title_modal').html("Edit Category");
            var id = $(this).attr('data-id');
            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("edit-subcategory-list") !!}/'+id,
                success: function (data) {
                    if (data.status === true) {
                        $('#subcat_id').val(id);
                        // $('#subcat').html(data.category_list);
                        $('#code').val(data.code);
                        // $("#category option[value="+data.kategori_id+"]").attr('selected', true); 
                        $('#cat_name').val(data.name);
                    }
                },
                fail: function (e) {
                    toastr.error(data.msg);
                }
            });
        });
        
        $('body').on('click', '.save-cat', function(){
            var form = $('#formSubKategori');
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
                                url: '{!! url("save-subcategory-list") !!}',
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
                                    $('#add-category').modal('toggle');
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

        $('body').on('click', '.del-cat', function(){
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
                                url: '{!! url("delete-subcategory-list") !!}/'+id,
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

        $('body').on('click', '.restore-cat', function(){
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
                                url: '{!! url("restore-subcategory-list") !!}/'+id,
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