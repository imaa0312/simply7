@extends('adminlte::layouts.app')

@section('htmlheader_title')
    Kategori Barang
@endsection

@section('contentheader_title')
    Kategori Barang
@endsection

@section('treeview_master','active')
@section('treeview_produk','active')
@section('treeview_kategori_produk','active')

@section('customcss')
	<link rel="stylesheet" href="{{ URL::asset('css/select2-bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{URL::asset('css/datatables.min.css')}}">
	<link rel="stylesheet" href="{{URL::asset('plugins/select2.min.css')}}">
@stop

@section('main-content')
    <div class="box box-primary">
		<div class="box-body">
			<div class="">
				<a href="{{url('/admin/produk/kategori/create')}}" class="btn btn-success btn-md">
					<i class="fa fa-plus"></i> Tambah Data
				</a>
				{{-- <a href="{{url('admin/report-master-jenis-barang/view')}}" class="btn btn-default btn-md">
                    <i class="fa fa-file"></i> View Report
                </a>
				<a href="{{url('admin/report-excel-jenis-barang')}}" class="btn bg-green color-palette">
                    <i class="fa fa-file-excel-o"></i> Export To Excel
                </a>
                <a href="{{url('admin/report-master-jenis-barang/download')}}" class="btn btn-warning btn-md">
                    <i class="fa fa-file-pdf-o"></i> Export to PDF --}}
                </a>
			</div>
			@include('admin.displayerror')
			<table class="table table-striped table-hover table-responsive" id="table2">
				<thead>
					<tr>
						<th>No.</th>
						<th>Kategori Barang</th>
						<th class="nosort" width="10%">Aksi</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
@stop
@section('scripts')
	<script type="text/javascript" src="{{URL::asset('/plugins/select2.full.min.js')}}"></script>
	<script type="text/javascript" src="{{URL::asset('/js/jquery.dataTables.min.js')}}"></script>
	<script type="text/javascript" src="{{URL::asset('/js/datatables.bootstrap.min.js')}}"></script>
	<script type="text/javascript">
	    var langId = "{{asset('vendor/select2/js/i18n/id.js')}}";
	    $(document).ready(function () {
	        $("#select2").select2();

            $('#table2').DataTable({
                "language": {
                    "emptyTable": "Data Kosong",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ Data",
                    "zeroRecords": "Tidak Ada Data yang Ditampilkan",
                    "oPaginate": {
                        "sFirst": "Awal",
                        "sLast": "Akhir",
                        "sNext": "Selanjutnya",
                        "sPrevious": "Sebelumnya"
                    },
                },

                processing: true,
                serverSide: true,
                ajax: '<?= url("/kategori/api/kategori") ?>',
                columns: [
                    {data: 'DT_Row_Index',searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });
	    })
	</script>
@stop
