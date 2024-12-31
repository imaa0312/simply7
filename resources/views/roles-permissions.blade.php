<?php $page = 'roles'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Roles & Permission
                @endslot
                @slot('li_1')
                    Manage your roles
                @endslot
            @endcomponent

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
                                    <th>Role Name</th>
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
                    url: "{!! url('roles-datatables') !!}",
                    type: "get"
                },
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'name', name: 'name'}
                ],            
                "bFilter": true,
                "sDom": 'fBtlpi',  
                "ordering": true,
                "autoWidth" : true,
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
        });
    </script>
@endsection
