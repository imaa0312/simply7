<?php $page = 'purchase-order'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header transfer">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Purchase Order</h4>
                        <h6>Manage your purchases</h6>
                    </div>
                </div>
                <div class="d-flex purchase-pg-btn">
                    <div class="page-btn">
                        <a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-units"><i
                                data-feather="plus-circle" class="me-2"></i>Add New Purchase</a>
                    </div>
                </div>

            </div>

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
                    
                    <div class="table-responsive product-list">
                        <table class="table  datanew list">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>Supplier Name</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Grand Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Created by</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Apex Computers</td>
                                    <td>PT001 </td>
                                    <td>19 Jan 2023</td>
                                    <td><span class="badges status-badge">Received</span></td>
                                    <td>$550</td>
                                    <td>$550</td>
                                    <td>$0.00</td>
                                    <td><span class="badge-linesuccess">Paid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Beats Headphones</td>
                                    <td>PT002 </td>
                                    <td>27 Jan 2023</td>
                                    <td><span class="badges status-badge">Received</span></td>
                                    <td>$370</td>
                                    <td>$370</td>
                                    <td>$0.00</td>
                                    <td><span class="badge-linesuccess">Paid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Dazzle Shoes</td>
                                    <td>PT003 </td>
                                    <td>08 Feb 2023</td>
                                    <td><span class="badges status-badge ordered">Ordered</span></td>
                                    <td>$400</td>
                                    <td>$400</td>
                                    <td>$200</td>
                                    <td><span class="badges-warning">Partial</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Best Accessories</td>
                                    <td>PT004 </td>
                                    <td>16 Feb 2023</td>
                                    <td><span class="badges unstatus-badge">Pending</span></td>
                                    <td>$560</td>
                                    <td>$0.00</td>
                                    <td>$560</td>
                                    <td><span class="badge badge-linedangered">Unpaid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>A-Z Store</td>
                                    <td>PT005</td>
                                    <td>12 Mar 2023</td>
                                    <td><span class="badges unstatus-badge">Pending</span></td>
                                    <td>$240</td>
                                    <td>$0.00</td>
                                    <td>$240</td>
                                    <td><span class="badge badge-linedangered">Unpaid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Hatimi Hardwares</td>
                                    <td>PT006</td>
                                    <td>24 Mar 2023</td>
                                    <td><span class="badges status-badge">Received</span></td>
                                    <td>$170</td>
                                    <td>$170</td>
                                    <td>$0.00</td>
                                    <td><span class="badge-linesuccess">Paid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Aesthetic Bags</td>
                                    <td>PT007</td>
                                    <td>06 Apr 2023</td>
                                    <td><span class="badges unstatus-badge">Pending</span></td>
                                    <td>$230</td>
                                    <td>$0.00</td>
                                    <td>$230</td>
                                    <td><span class="badge badge-linedangered">Unpaid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Alpha Mobiles</td>
                                    <td>PT008</td>
                                    <td>14 Apr 2023</td>
                                    <td><span class="badges status-badge ordered">Ordered</span></td>
                                    <td>$300</td>
                                    <td>$150</td>
                                    <td>$300</td>
                                    <td><span class="badges-warning">Partial</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Sigma Chairs</td>
                                    <td>PT009</td>
                                    <td>02 May 2023</td>
                                    <td><span class="badges unstatus-badge">Pending</span></td>
                                    <td>$620</td>
                                    <td>$0.00</td>
                                    <td>$620</td>
                                    <td><span class="badge badge-linedangered">Unpaid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>Zenith Bags</td>
                                    <td>PT010</td>
                                    <td>23 May 2023</td>
                                    <td><span class="badges status-badge">Received</span></td>
                                    <td>$200</td>
                                    <td>$200</td>
                                    <td>$0.00</td>
                                    <td><span class="badge-linesuccess">Paid</span></td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="javascript:void(0);">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-units">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="confirm-text p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

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
            $('#supp-list').select2({
                ajax: {
                    type: "GET",
                    dataType: 'json',
                    url: '{!! url("getSupp") !!}',
                    data: function (param) {
                        return {
                            q: param.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    }
                },
                dropdownParent: $("#add-units")
            });

            $('#prod-list').select2({
                ajax: {
                    type: "GET",
                    dataType: 'json',
                    url: '{!! url("pos-prod") !!}',
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
                    }
                },
                dropdownParent: $("#add-units")
            });

            $('#myTable2').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('po-product-datatables') !!}/"+$('#po_id').val(),
                    type: "get"
                },
                // select: {
                //     style: 'multi',
                //     selector: 'td:first-child'
                // },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'qty', name: 'qty'},
                    {data: 'purchase_price', name: 'purchase_price'},
                    {data: 'purchase_discount', name: 'purchase_discount'},
                    {data: 'total_cost', name: 'total_cost'}
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

            $('body').on('click', '#add-product', function(){
                var form = $('#formKu');
                var formdata = new FormData(form[0]);

                $.ajax({
                    method : "POST",
                    dataType: 'json',
                    url: '{!! url("save-po-temp") !!}',
                    data: formdata,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === false) {
                            Swal.fire({
                                position: "top-end",
                                toast: true,
                                title: "Something wen't Wrong",
                                icon: "error",
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                        // $('#add-category').modal('toggle');
                        $('#myTable2').DataTable().ajax.reload();
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
