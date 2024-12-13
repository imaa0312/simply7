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
<link rel="stylesheet" href="{{URL::asset('plugins/select2.min.css')}}">
@stop

@section('main-content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-info">
            {{-- <div class="box-header with-border">
                <h3 class="box-title">Kategori Barang</h3>
            </div> --}}
            <!-- /.box-header -->
            <!-- form start -->
            <form class="form-horizontal" action="{{url('/admin/produk/kategori')}}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            {{ method_field('post') }}
                <div class="box-body">
                    @include('admin.displayerror')
                    <div class="form-group">
                        <label class="col-sm-2 col-sm-offset-1 control-label">Kategori Barang</label>
                        <div class="col-sm-8">
                            <input type="text" name="name" class="form-control" placeholder="Kategori Barang...">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <a href="{{ url('/admin/produk/kategori') }}" class="btn btn-info">Kembali</a>
                        <input type="reset" class="btn btn-danger" value="Batal">
                        <input  type="submit" class="btn btn-success" value="Simpan">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



@stop
@section('scripts')
<script type="text/javascript" src="{{URL::asset('/plugins/select2.full.min.js')}}"></script>
<script type="text/javascript">
    var langId = "{{asset('vendor/select2/js/i18n/id.js')}}";
    $(document).ready(function () {
        $("#select2").select2();
    })
</script>
@stop
