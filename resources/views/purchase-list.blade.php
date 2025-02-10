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
                        <a href="#" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-units" id="add-po"><i
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
                        <table class="table" id="myTable">
                            <thead>
                                <tr>
                                    <th class="no-sort">No</th>
                                    <th>Supplier Name</th>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Grand Total</th>
                                    <th>Created by</th>
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
            var gt = 0, total = 0;

            function formatRupiah(angka) {
                let number_string = angka.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }
            
            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{!! url('po-datatables') !!}",
                    type: "get"
                },
                // select: {
                //     style: 'multi',
                //     selector: 'td:first-child'
                // },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, "className": "dt-center" },
                    {data: 'supplier', name: 'supplier'},
                    {data: 'refno', name: 'refno'},
                    {data: 'po_date', name: 'po_date'},
                    {data: 'status', name: 'status'},
                    {data: 'grand_total', name: 'grand_total'},
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
                    delay: 250,
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
                    },
                    cache: true
                },
                dropdownParent: $("#add-units")
            });

            $('#supp-list').select2('open');

            $('body').on('change', '#prod-list', function(){
                $('#qty').val(1);
            });

            $('body').on('click', '#add-po', function(){
                $('#mode').val("add");

                gt = 0;
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
                        if (data.status === true) {
                            $('#po_id').val(data.id);                            
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

                $('#refno').val("");
                $('#date').val();
                $('#prod-list').val(null).trigger('change');
                $('#qty').val(0);
                $('#order_tax').val(0);
                $('#order_discount').val(0);
                $('#shipping_cost').val(0);
                $('#grand_total').val(0);
                $('#desc').val("");
                $('#myTable2').DataTable().clear();
                $('#myTable2').DataTable().destroy();

                $('#refno').prop('disabled', false);
                $('#date').prop('disabled', false);
                $('#order_tax').prop('disabled', false);
                $('#order_discount').prop('disabled', false);
                $('#shipping_cost').prop('disabled', false);
                $('#grand_total').prop('disabled', false);
                $('#desc').prop('disabled', false);
                $('.product-section').show();
                $('#title-PO').html("Purchase Order");
                $('#supp-section').hide();
                $("#supp-list").select2().next().show();
            });

            $('body').on('click', '#add-product', function(){
                var form = $('#formKu');
                var formdata = new FormData(form[0]);

                $.ajax({
                    method : "POST",
                    dataType: 'json',
                    url: '{!! url("save-po-product") !!}',
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
                        $('#po_id').val(data.id);
                        $('#prod-list').val(null).trigger('change');
                        $('#qty').val(0);
                        $('#myTable2').DataTable().clear();
                        $('#myTable2').DataTable().destroy();
                        $('#myTable2').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: "{!! url('po-producttemp-datatables') !!}/"+$('#po_id').val(),
                                type: "get"
                            },
                            // select: {
                            //     style: 'multi',
                            //     selector: 'td:first-child'
                            // },
                            columns: [
                                {data: 'name', name: 'name'},
                                {data: 'total_qty', name: 'total_qty'},
                                {data: 'total_tax', name: 'total_tax'},
                                {data: 'total_discount', name: 'total_discount'},
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

                        total = total + data.total;
                        gt = total + parseFloat($('#shipping_cost').val().replace('.', '')) - parseFloat($('#order_discount').val().replace('.', ''));
                        gt = gt + parseFloat($('#order_tax').val().replace('.', '')/100*gt);
                        
                        $('#grand_total').val(formatRupiah(gt.toString()));
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

            $('body').on('click', '.close-po', function(){
                if($('#mode').val()  == 'add'){
                    $.ajax({
                        method : "GET",
                        dataType: 'json',
                        url: '{!! url("del-po-temp") !!}/'+$('#po_id').val(),
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data.status === true) {
                                $('#po_id').val(0);
                                $('#myTable2').DataTable().clear();
                                $('#myTable2').DataTable().destroy();
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
            });

            $('body').on('click', '#save-po', function(){
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
                                    url: '{!! url("save-po") !!}',
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
                                        $('#order_tax').val(0);
                                        $('#order_discount').val(0);
                                        $('#shipping_cost').val(0);
                                        $('#grand_total').val(0);
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

            $('body').on('click', '.edit-po', function(){
                $('#mode').val("edit");
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-po") !!}/'+$(this).attr('data-id'),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            gt = data.grand_total;
                            
                            $('#refno').val(data.refno);
                            $('#date').val(data.po_date);
                            $('#prod-list').val(null).trigger('change');
                            $('#qty').val(0);
                            $('#order_tax').val(data.order_tax_percent);
                            $('#order_discount').val(data.order_discount);
                            $('#shipping_cost').val(data.shipping_cost);
                            $('#grand_total').val(data.grand_total);
                            $('#desc').val(data.description);
                            $('#title-PO').html("Purchase Order to Supplier : "+data.nama_supp)

                            $('#refno').prop('disabled', false);
                            $('#date').prop('disabled', false);
                            $('#order_tax').prop('disabled', false);
                            $('#order_discount').prop('disabled', false);
                            $('#shipping_cost').prop('disabled', false);
                            $('#grand_total').prop('disabled', false);
                            $('#desc').prop('disabled', false);
                            $('.product-section').show();
                            $('#supp-section').hide();
                            $("#supp-list").select2().next().show();

                            $('#myTable2').DataTable().clear();
                            $('#myTable2').DataTable().destroy();
                            $('#myTable2').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: "{!! url('po-product-datatables') !!}/"+data.id,
                                    type: "get"
                                },
                                columns: [
                                    {data: 'name', name: 'name'},
                                    {data: 'total_qty', name: 'total_qty'},
                                    {data: 'total_discount', name: 'total_discount'},
                                    {data: 'total_tax', name: 'total_tax'},
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
                            $("#supp-list").val(data.supplier);
                            $("#supp-list").trigger("change");
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

            $('body').on('click', '.del-po', function(){
                var id = $(this).attr('data-id');
                bootbox.dialog({
                    message: "Are you sure want to cancel this PO ?",
                    title: "Save Confirmation",
                    buttons: {
                        success: {
                            label: "Yes",
                            className: "btn-success btn-flat",
                            callback: function () {
                                $.ajax({
                                    method : "GET",
                                    dataType: 'json',
                                    url: '{!! url("del-po") !!}/'+id,
                                    cache: false,
                                    processData: false,
                                    contentType: false,
                                    success: function (data) {
                                        if (data.status === true) {
                                            Swal.fire({
                                                position: "top-end",
                                                toast: true,
                                                icon: "success",
                                                title: "PO has been cancelled",
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

            $('body').on('click', '.view-po', function(){
                $('#mode').val("view");
                $.ajax({
                    method : "GET",
                    dataType: 'json',
                    url: '{!! url("edit-po") !!}/'+$(this).attr('data-id'),
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.status === true) {
                            gt = data.grand_total;
                            $('#supp-list').val(data.supplier);
                            $('#refno').val(data.refno);
                            $('#date').val(data.po_date);
                            $('#order_tax').val(data.order_tax_percent);
                            $('#order_discount').val(data.order_discount);
                            $('#shipping_cost').val(data.shipping_cost);
                            $('#grand_total').val(data.grand_total);
                            $('#desc').val(data.description);
                            $('#refno').prop('disabled', true);
                            $('#date').prop('disabled', true);
                            $('#order_tax').prop('disabled', true);
                            $('#order_discount').prop('disabled', true);
                            $('#shipping_cost').prop('disabled', true);
                            $('#grand_total').prop('disabled', true);
                            $('#desc').prop('disabled', true);
                            $('.product-section').hide();
                            $('#supp-section').show();
                            $('#supp-section').val(data.nama_supp);
                            $("#supp-list").select2().next().hide();
                            $('#title-PO').html("Purchase Order to Supplier : "+data.nama_supp)

                            $('#myTable2').DataTable().clear();
                            $('#myTable2').DataTable().destroy();
                            $('#myTable2').DataTable({
                                processing: true,
                                serverSide: true,
                                ajax: {
                                    url: "{!! url('po-product-datatables') !!}/"+data.id,
                                    type: "get"
                                },
                                columns: [
                                    {data: 'name', name: 'name'},
                                    {data: 'total_qty', name: 'total_qty'},
                                    {data: 'total_discount', name: 'total_discount'},
                                    {data: 'total_tax', name: 'total_tax'},
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
                            $("#supp-list").val(data.supplier);
                            $("#supp-list").trigger("change");
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

            $('body').on('change', '#order_tax', function(){
                var atotal = total - parseFloat($('#order_discount').val().replace('.', ''));
                gt = atotal + parseFloat($(this).val().replace('.', '')/100*atotal) + parseFloat($('#shipping_cost').val().replace('.', ''));
                        
                $('#grand_total').val(formatRupiah(gt.toString()));
            });

            $('body').on('change', '#order_discount', function(){
                var atotal = total - parseFloat($(this).val().replace('.', ''));
                gt = atotal + parseFloat($('#order_tax').val().replace('.', '')/100*atotal) + parseFloat($('#shipping_cost').val().replace('.', ''));
                        
                $('#grand_total').val(formatRupiah(gt.toString()));
            });

            $('body').on('change', '#shipping_cost', function(){
                var atotal = total - parseFloat($('#order_discount').val().replace('.', ''));
                gt = atotal + parseFloat($('#order_tax').val().replace('.', '')/100*atotal) + parseFloat($(this).val().replace('.', ''));
                       
                $('#grand_total').val(formatRupiah(gt.toString()));
            });

            $('body').on('keypress', '.currency', function(e) {
                let value = $(this).val();
                if (value === '0' && e.which !== 46) { // 46 adalah kode untuk '.'
                    $(this).val('');
                }
            });

            $('body').on('input', '.currency', function() {
                let value = $(this).val();
                $(this).val(formatRupiah(value));
            });
        });
    </script>
@endsection
