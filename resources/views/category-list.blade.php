<?php $page = 'category-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            @component('components.breadcrumb')
                @slot('title')
                    Category
                @endslot
                @slot('li_1')
                    Manage your categories
                @endslot
                @slot('li_2')
                    Add Category
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
                        <table class="table  datanew">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>Category</th>
                                    <th>Created On</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($getDataKategori as $cat)
                                    <tr>
                                        <td>
                                            <label class="checkboxs">
                                                <input type="checkbox">
                                                <span class="checkmarks"></span>
                                            </label>
                                        </td>
                                        <td>{{{ $cat->name }}}</td>
                                        <td>{{{ date("d M Y", strtotime($cat->created_at)) }}}</td>
                                        <td class="action-table-data">
                                            <div class="edit-delete-action">
                                                <a class="me-2 p-2 edit-cat" href="javascript:void(0);" data-bs-toggle="modal"
                                                    data-bs-target="#edit-category" data-id="{{{ $cat->id }}}">
                                                    <i data-feather="edit" class="feather-edit"></i>
                                                </a>
                                                <a class="confirm-text p-2 del-cat" href="javascript:void(0);">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /product list -->
        </div>
    </div>


<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
<!-- Datatable JS -->
<script src="{{ URL::asset('build/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function(){
        $('body').on('click', '.edit-cat', function(){
            // alert("s")
            var id = $(this).attr('data-id');
            $.ajax({
                type : "GET",
                dataType: 'json',
                url: '{!! url("edit-category-list") !!}/'+id,
                success: function (data) {
                    if (data.status === true) {
                        $('#cat_id').val(id);
                        $('#cat_value').val(data.name);
                    }
                },
                fail: function (e) {
                    iziToast.error({
                        message: e,
                        position: 'topRight'
                    });
                }
            });
        });
    });
</script>
@endsection