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
                                    <th>Store Address </th>
                                    <th>Store Manager </th>
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
                    {                          
                        orderable: false,
                        targets: 0,
                        'checkboxes': {
                            'selectRow': true,
                        },
                        defaultContent: '',
                        data: 'checkbox'
                    },
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
            });
        });
    </script>
@endsection
