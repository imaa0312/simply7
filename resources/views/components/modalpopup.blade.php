@if (Route::is(['suppliers']))
    <!-- Add Supplier -->
    <div class="modal fade bd-example-modal-lg" id="add-supplier">
        <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Add Supplier</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" name="sup_id" id="sup_id">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Supplier Name</label>
                                            <input type="text" class="form-control" id="sup_name" name="sup_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="inputGroup-sizing-default">+62</span>
                                                <input type="text" class="form-control" id="telp" name="phone">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Address</label>
                                            <textarea class="form-control mb-1" id="address" name="address" maxlength="300"></textarea>
                                            <p>Maximum 300 Characters</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-10 col-10">
                                        <div class="input-blocks" id="prov_list">
                                            <label>Province</label>
                                            <select class="select" id="prov" name="prov">
                                                <option>Choose Province</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-10 col-10">
                                        <div class="input-blocks" id="city_list">
                                            <label>City</label>
                                            <select class="select" id="city" name="city">
                                                <option>Choose City</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-0 input-blocks">
                                            <label class="form-label">Descriptions</label>
                                            <textarea class="form-control mb-1" id="desc" name="desc" maxlength="600"></textarea>
                                            <p>Maximum 600 Characters</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-sup">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Supplier -->
@endif

@if (Route::is(['warehouse']))
    <!-- Add Supplier -->
    <div class="modal fade bd-example-modal-lg" id="add-warehouse">
        <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Add Warehouse</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" name="warehouse_id" id="warehouse_id">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Warehouse Name</label>
                                            <input type="text" class="form-control" id="warehouse_name" name="warehouse_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="inputGroup-sizing-default">+62</span>
                                                <input type="text" class="form-control" id="telp" name="phone">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Address</label>
                                            <textarea class="form-control mb-1" id="address" name="address" maxlength="300"></textarea>
                                            <p>Maximum 300 Characters</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-warehouse">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Supplier -->
@endif

@if (Route::is(['category-list']))
    <!-- Add Category -->
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title-modal">Create Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="formKategori">
                                @csrf
                                <input type="hidden" name="cat_id" id="cat_id">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" name="cat_name" id="cat_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-cat">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if (Route::is(['subcategory-list']))
    <!-- Add Category -->
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Create Sub Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="formSubKategori" method="POST">
                                @csrf
                                <input type="hidden" name="subcat_id" id="subcat_id">
                                <!--<div class="mb-3" id="subcat">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option>Choose Category</option>
                                        <option>Category</option>
                                    </select>
                                </div>-->
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="cat_name" id="cat_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-cat">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if (Route::is(['ssubcategory-list']))
    <!-- Add Category -->
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Create Sub Sub Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="formSubKategori" method="POST">
                                @csrf
                                <input type="hidden" name="subcat_id" id="subcat_id">
                                <!--<div class="mb-3" id="subcat">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option>Choose Category</option>
                                        <option>Category</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="ssubcat">
                                    <label class="form-label">Sub Category</label>
                                    <select class="form-control" id="subcategory" name="subcategory">
                                        <option>Choose Sub Category</option>
                                        <option>Sub Category</option>
                                    </select>
                                </div>-->
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="cat_name" id="cat_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-cat">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if (Route::is(['sssubcategory-list']))
    <!-- Add Category -->
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Create Sub-Sub-Sub Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="formSubKategori" method="POST">
                                @csrf
                                <input type="hidden" name="subcat_id" id="subcat_id">
                                <!-- <div class="mb-3" id="subcat">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option>Choose Category</option>
                                        <option>Category</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="ssubcat">
                                    <label class="form-label">Sub Category</label>
                                    <select class="form-control" id="subcategory" name="subcategory">
                                        <option>Choose Sub Category</option>
                                        <option>Sub Category</option>
                                    </select>
                                </div>                                
                                <div class="mb-3" id="sssubcat">
                                    <label class="form-label">Sub-Sub Category</label>
                                    <select class="form-control" id="ssubcategory" name="ssubcategory">
                                        <option>Choose Sub-Sub Category</option>
                                        <option>Sub-Sub Category</option>
                                    </select>
                                </div> -->
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="cat_name" id="cat_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-cat">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if (Route::is(['brand-list']))
    <!-- Add Category -->
    <div class="modal fade" id="add-brand">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Create Brand</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="formKu" method="POST">
                                @csrf
                                <input type="hidden" name="brand_id" id="brand_id">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="brand_name" id="brand_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-brand">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if (Route::is(['size']))
    <!-- Add Category -->
    <div class="modal fade" id="add-size">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4 id="title_modal">Create Size</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="formKu" method="POST">
                                @csrf
                                <input type="hidden" name="size_id" id="size_id">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="size_name" id="size_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code</label>
                                    <input type="text" class="form-control" name="code" id="code">
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-size">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->
@endif

@if(Route::is(['expense-category']))
    <!-- Add Expense Category-->
    <div class="modal fade" id="add-expense-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Expense Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" name="exp_cat_id" id="exp_cat_id">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label">Expense Name</label>
                                            <input type="text" class="form-control" name="nama" id="nama">
                                        </div>
                                        
                                    </div>								
                                    <!-- Editor -->
                                    <div class="col-md-12">
                                        <div class="edit-add card">
                                            <div class="edit-add">
                                                <label class="form-label">Description</label>
        
                                            </div>
                                            <div class="card-body-list input-blocks mb-0">
                                                <textarea class="form-control" name="desc" id="desc"></textarea>
                                            </div>
                                            <p>Maximum 600 Characters</p>
                                        </div>
                                    </div>
                                    <!-- /Editor -->
                                </div>									
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-expense-category">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Expense Category-->
@endif

@if(Route::is(['expense-list']))
    <!-- Add Expense -->
    <div class="modal fade" id="add-expense">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Expense</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" name="expense_id" id="expense_id">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3" id="category_list">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Date</label>
                                        <input type="text" id="date" name="tanggal" class="form-control">
                                        <span class="form-text text-muted">dd/mm/yyyy</span>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Amount</label>
                                            <input type="text" class="form-control" placeholder="Rp." id="amount" name="amount">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Reference</label>
                                            <input type="text" class="form-control" id="reference" name="reference">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label">Expense For</label>
                                            <input type="text" class="form-control" name="nama" id="nama">
                                            <span class="unlimited-text">0 for Unlimited</span>
                                        </div>
                                        
                                    </div>								
                                    <!-- Editor -->
                                    <div class="col-md-12">
                                        <div class="edit-add card">
                                            <div class="edit-add">
                                                <label class="form-label">Description</label>
                                            </div>
                                            <div class="card-body-list input-blocks mb-0">
                                                <textarea class="form-control" name="desc" id="desc"></textarea>
                                            </div>
                                            <p>Maximum 600 Characters</p>
                                        </div>
                                    </div>
                                    <!-- /Editor -->
                                </div>							
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-expense">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Expense -->
@endif

@if (Route::is(['store-list']))
    <!-- Add Store -->
    <div class="modal fade" id="add-stores">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Create Store</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" class="form-control" name="store_id" id="store_id">
                                <div class="mb-3">
                                    <label class="form-label">Store Name</label>
                                    <input type="text" class="form-control" name="store_name" id="store_name">
                                </div>                                
                                <div class="col-lg-12">
                                    <div class="input-blocks" id="manager_list">
                                        <label>Store Manager</label>
                                        <select class="select" id="manager" name="manager">
                                            <option>Choose Manager</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Store Phone</label>
                                    <input type="text" class="form-control" name="telp" id="telp">
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-0 input-blocks">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control mb-1" name="address" id="address"></textarea>
                                        <p>Maximum 100 Characters</p>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-store">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Store -->
@endif

@if (Route::is(['customers']))
    <!-- Add Customer -->
    <div class="modal fade bs-example-modal-lg" id="add-customers">
        <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Customer</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form method="POST" id="myForm">
                                @csrf
                                <input type="hidden" id="customers_id" name="customers_id">
                                <div class="row">
                                    <div class="col-lg-4 pe-0">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="nama" id="nama">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 pe-0">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 pe-0">
                                        <div class="input-blocks">
                                            <label class="mb-2">Phone</label>
                                            <input class="form-control form-control-lg group_formcontrol"
                                                id="telp" name="telp" type="text">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 pe-0">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="address" id="address">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 pe-0">
                                        <div class="mb-3" id="prov_list">
                                            <label class="form-label">Province</label>
                                            <select class="select" id="prov" name="prov">
                                                <option>Choose Province</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 pe-0">
                                        <div class="mb-3" id="city_list">
                                            <label class="form-label">City</label>
                                            <select class="select" id="city" name="city">
                                                <option>Choose City</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3 input-blocks">
                                            <label class="form-label">Descriptions</label>
                                            <textarea class="form-control mb-1" id="desc" name="desc"></textarea>
                                            <p>Maximum 60 Characters</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-customers">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Customer -->
@endif

@if(Route::is(['purchase-order']))
	<!-- Add Purchase -->
	<div class="modal fade" id="add-units">
		<div class="modal-dialog purchase modal-dialog-centered stock-adjust-modal">
			<div class="modal-content">
				<div class="page-wrapper-new p-0">
					<div class="content">
						<div class="modal-header border-0 custom-modal-header">
							<div class="page-title">
								<h4 id="title-PO">Add Purchase</h4>
							</div>
							<button type="button" class="close close-po" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body custom-modal-body">
							<form method="POST" id="formKu">
                                @csrf
                                <input type="hidden" id="po_id" name="po_id" value="0">
                                <input type="hidden" id="mode" name="mode">
								<div class="row">
									<div class="col-lg-4 col-md-6 col-sm-12">
										<div class="input-blocks add-product">
											<label>Supplier Name</label>
                                            <select class="select2 form-control" id="supp-list" name="supplier">
                                                <option value="0">Search Supplier</option>
                                            </select>
                                            <input type="text" class="form-control" id="supp-section" readonly style="background-color: #f8f9fa;">
										</div>
									</div>
									<div class="col-lg-4 col-md-6 col-sm-12">
										<div class="input-blocks">
											<label>Reference No</label>
											<input type="text" class="form-control" id="refno" readonly style="background-color: #f8f9fa;">
										</div>
									</div>
									<div class="col-lg-4 col-md-6 col-sm-12">
										<div class="input-blocks">
											<label>Purchase Date</label>
                                            <input type="text" id="date" name="po_date" class="form-control">
                                            <span class="form-text text-muted">dd/mm/yyyy</span>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-6 product-section">
										<div class="input-blocks">
											<label>Product Name</label>
											<select class="select2 form-control" id="prod-list" name="product">
                                                <option value="0">Search Products</option>
                                            </select>
										</div>
									</div>
									<div class="col-lg-2 product-section">
										<div class="input-blocks">
											<label>Qty</label>
                                            <div class="row">
                                                <div class="col-lg-8 col-sm-8 col-8">
											        <input type="number" min=1 value="1" class="form-control" name="qty" id="qty">
                                                </div>
                                                <div class="col-lg-4 col-sm-4 col-4 ps-0">
                                                    <div class="add-icon tab">
                                                        <a href="javascript:void(0);" id="add-product"><i data-feather="plus-circle" class="feather-plus-circles"></i></a>
                                                    </div>
                                                </div>
                                            </div>
										</div>
									</div>
									<div class="col-lg-12">
										<div class="modal-body-table">
											<div class="table-responsive">
												<table class="table" id="myTable2">
													<thead>
														<tr>
															<th>Product</th>
															<th>Qty</th>
															<th>Purchase Price(Rp)</th>
															<th>Discount(Rp)</th>
															<th>Total Cost(Rp)</th>
														</tr>
													</thead>

													<tbody>
													</tbody>
												</table>
											</div>
										</div>

									</div>
									<div class="row">
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="input-blocks">
												<label>Order Tax (%)</label>
												<input type="number" class="form-control" min=0 name="order_tax" id="order_tax">
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="input-blocks">
												<label>Discount (Rp)</label>
												<input class="currency" type="text" value="0" name="order_discount" id="order_discount">
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="input-blocks">
												<label>Shipping (Rp)</label>
												<input class="currency" type="text" value="0" id="shipping_cost" name="shipping_cost">
											</div>
										</div>
										<div class="col-lg-3 col-md-6 col-sm-12">
											<div class="input-blocks">
												<label>Grand Total</label>
                                                <input type="text" id="grand_total" name="grand_total" readonly style="background-color: #f8f9fa;">
											</div>
										</div>
									</div>
								</div>

								<div class="col-lg-12">
									<div class="input-blocks summer-description-box">
										<label>Notes</label>
										<textarea name="desc" id="desc"></textarea>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2 close-po" data-bs-dismiss="modal">Cancel</button>
										<button type="button" class="btn btn-submit" id="save-po">Submit</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Add Purchase -->
@endif

@if(Route::is(['product-list']))
	<div class="modal fade" id="add-products">
		<div class="modal-dialog purchase modal-dialog-centered stock-adjust-modal">
			<div class="modal-content">
				<div class="page-wrapper-new p-0">
					<div class="content">
						<div class="modal-header border-0 custom-modal-header">
							<div class="page-title">
								<h4>Add Product</h4>
							</div>
							<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body custom-modal-body">
							<form method="POST" id="formKu">
                                @csrf
                                <input type="hidden" name="product_id" id="product_id">
                                <input type="hidden" name="images_id" id="images_id">
                                <div class="card">
                                    <div class="card-body add-product pb-0">
                                        <div class="accordion-card-one accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <div class="accordion-header" id="headingOne">
                                                    <div class="accordion-button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                        aria-controls="collapseOne">
                                                        <div class="addproduct-icon">
                                                            <h5><i data-feather="info" class="add-info"></i><span>Product Information</span>
                                                            </h5>
                                                            <a href="javascript:void(0);"><i data-feather="chevron-down"
                                                                    class="chevron-down-add"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                                                    data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="mb-3 add-product">
                                                                    <label class="form-label">Product Name</label>
                                                                    <input type="text" class="form-control" name="product_name" id="product_name">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="mb-3 add-product" id="category_list">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="mb-3 add-product" id="sub_category_list">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="mb-3 add-product" id="ssub_category_list">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6 col-12">
                                                                <div class="mb-3 add-product" id="sssub_category_list">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="add-product-new">
                                                            <div class="row">
                                                                <div class="col-lg-4 col-sm-6 col-12">
                                                                    <div class="mb-3 add-product" id="brand_list">
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-6 col-12">
                                                                    <div class="mb-3 add-product" id="size_list">
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-4 col-sm-6 col-12">
                                                                    <div class="input-blocks add-product list">
                                                                        <label>SKU</label>
                                                                        <input type="text" class="form-control list" id="sku" name="sku" readonly>
                                                                        <button type="submit" class="btn btn-primaryadd">
                                                                            Generate Code
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Editor -->
                                                        <div class="col-lg-12">
                                                            <div class="input-blocks summer-description-box transfer mb-3">
                                                                <label>Description</label>
                                                                <textarea class="form-control h-100" rows="5" id="desc" name="desc"></textarea>
                                                                <p class="mt-1">Maximum 60 Characters</p>
                                                            </div>
                                                        </div>
                                                        <!-- /Editor -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-card-one accordion" id="accordionExample2">
                                            <div class="accordion-item">
                                                <div class="accordion-header" id="headingTwo">
                                                    <div class="accordion-button" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                        aria-controls="collapseTwo">
                                                        <div class="text-editor add-list">
                                                            <div class="addproduct-icon list icon">
                                                                <h5><i data-feather="life-buoy" class="add-info"></i><span>Pricing</span></h5>
                                                                <a href="javascript:void(0);"><i data-feather="chevron-down"
                                                                        class="chevron-down-add"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="collapseTwo" class="accordion-collapse collapse show"
                                                    aria-labelledby="headingTwo" data-bs-parent="#accordionExample2">
                                                    <div class="accordion-body">
                                                        <div class="tab-content" id="pills-tabContent">
                                                            <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                                                                aria-labelledby="pills-home-tab">
                                                                <div class="row">
                                                                    <div class="col-lg-3 col-sm-6 col-12">
                                                                        <div class="input-blocks add-product">
                                                                            <label>Purchase Price</label>
                                                                            <input type="text" class="form-control" id="p_price" name="p_price">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-6 col-12">
                                                                        <div class="input-blocks add-product">
                                                                            <label>Sale Price</label>
                                                                            <input type="text" class="form-control" id="s_price" name="s_price">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-6 col-12">
                                                                        <div class="input-blocks add-product">
                                                                            <label>Profit Percentage</label>
                                                                            <input type="text" class="form-control" id="profit" name="profit">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-sm-6 col-12">
                                                                        <div class="input-blocks add-product">
                                                                            <label>Quantity Alert</label>
                                                                            <input type="text" class="form-control" id="qty_alert" name="qty_alert">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="accordion-card-one accordion" id="accordionExample3">
                                                                    <div class="accordion-item">
                                                                        <div class="accordion-header" id="headingThree">
                                                                            <div class="accordion-button" data-bs-toggle="collapse"
                                                                                data-bs-target="#collapseThree"
                                                                                aria-controls="collapseThree">
                                                                                <div class="addproduct-icon list">
                                                                                    <h5><i data-feather="image"
                                                                                            class="add-info"></i><span>Images</span></h5>
                                                                                    <a href="javascript:void(0);"><i
                                                                                            data-feather="chevron-down"
                                                                                            class="chevron-down-add"></i></a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div id="images_list">
                                                                        </div>
                                                                        <div id="collapseThree" class="accordion-collapse collapse show"
                                                                            aria-labelledby="headingThree"
                                                                            data-bs-parent="#accordionExample3">
                                                                            <div class="accordion-body">
                                                                                <div class="text-editor add-list add">
                                                                                    <div class="col-lg-12">
                                                                                        <div class="add-choosen">
                                                                                            <div class="input-blocks" id="addnew">
                                                                                                <form id="formImage" enctype="multipart/form-data" method="POST">
                                                                                                @csrf
                                                                                                <input type="hidden" name="images" id="images">
                                                                                                <div class="image-upload image-up">
                                                                                                    <input type="file" id="image" name="image">
                                                                                                    <div class="image-uploads">
                                                                                                        <i data-feather="plus-circle"
                                                                                                            class="plus-down-add me-0"></i>
                                                                                                        <h4>Add Images</h4>
                                                                                                    </div>
                                                                                                </div>
                                                                                                </form>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="btn-addproduct mb-4">
                                        <button type="button" class="btn btn-cancel me-2">Cancel</button>
                                        <button type="button" class="btn btn-submit save-product">Save Product</button>
                                    </div>
                                </div>
                            </form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif

@if(Route::is(['purchase-received']))
<div class="modal fade" id="add-received">
    <div class="modal-dialog add-centered">
        <div class="modal-content">
            <div class="page-wrapper p-0 m-0">
                <div class="content p-0">
                    <div class="modal-header border-0 custom-modal-header">
                        <div class="page-title">
                            <h4> Add Purchase Received</h4>
                        </div>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" id="formKu">
                                @csrf
                                <input type="hidden" id="pr_id" name="pr_id">
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="input-blocks">
                                            <label class="form-label">Reference No.</label>
                                            <select class="select2 form-control" id="refno" name="refno">
                                                <option value="0">Search</option>
                                            </select>
                                            <input type="text" class="form-control" id="refno_po" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="input-blocks">
                                            <label>PR Code</label>
                                            <input type="text" id="pr_code" name="pr_code" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-12">
                                        <div class="input-blocks">
                                            <label>Receive Date</label>
                                            <input type="text" id="date" name="pr_date" class="form-control prdate">
                                            <span class="form-text text-muted prdate">YYYY-MM-DD HH:MM</span>
                                            <input type="text" class="form-control" id="pr_date" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-sm-6 col-12 view">
                                        <div class="input-blocks">
                                            <label>Product Barcode</label>
                                            <div class="input-groupicon select-code">
                                                <input type="text" placeholder="Please type Product Barcode" id="barcode" name="barcode">
                                                <div class="addonset">
                                                    <img src="{{ URL::asset('/build/img/icons/qrcode-scan.svg')}}" alt="img">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive no-pagination">
                                    <table class="table" id="myTable2">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>

                                <br>
                                
                                <div class="row">
                                    <div class="col-lg-12 text-end">
                                        <button type="button"  class="btn btn-cancel add-cancel me-3" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-submit save-pr view">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(Route::is(['stock-transfer']))
<div class="modal fade" id="add-units">
    <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
        <div class="modal-content">
            <div class="page-wrapper-new p-0">
                <div class="content">
                    <div class="modal-header border-0 custom-modal-header">
                        <div class="page-title">
                            <h4>Add Transfer</h4>
                        </div>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <form method="POST" id="formKu">
                            @csrf
                            <input type="hidden" id="ts_id" name="ts_id">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label class="form-label">Transfer Stock Code</label>
                                        <input type="text" class="form-control" id="ts_code" disabled>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>Transfer Date</label>
                                        <input type="text" id="ts_date" name="ts_date" class="form-control">
                                        <span class="form-text text-muted">YYYY-MM-DD HH:MM</span>
                                        <input type="hidden" class="form-control" id="tsdate" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>From</label>
                                        <select class="form-control" id="asal" name="asal">
                                            <option>Choose</option>
                                            <option value="0">Warehouse</option>
                                            @foreach($data['store'] as $store)
                                                <option value="{{{ $store->id }}}">{{{ $store->name }}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-blocks">
                                        <label>To</label>
                                        <select class="form-control" id="tujuan" name="tujuan">
                                            <option>Choose</option>
                                        </select>
                                        <input type="text" class="form-control" id="tujuan_name">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="input-blocks search-form mb-3">
                                            <label>Product</label>
                                            <select class="select2 form-control" id="produk" name="produk">
                                                <option value="0">Search</option>
                                            </select>
                                            <input type="text" id="produk_name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-blocks search-form mb-3">
                                            <label>Qty</label>
                                            <input type="number" class="form-control" name="qty" id="qty">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks search-form mb-0">
                                        <label>Notes</label>
                                        <textarea class="form-control" id="desc" name="desc"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer-btn">
                                <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-submit save-ts">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
















@if (Route::is(['add-product']))
    <!-- Add Adjustment -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Variation Attribute</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="input-blocks">
                                        <label>Attribute Name</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks">
                                        <label>Add Value</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <ul class="nav user-menu">
                                        <li class="nav-item nav-searchinputs">
                                            <div class="top-nav-search">
                                                <form action="#" class="dropdown">
                                                    <div class="searchinputs list dropdown-toggle"
                                                        id="dropdownMenuClickable2" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="false">
                                                        <input type="text" placeholder="Search">
                                                        <i data-feather="search" class="feather-16 icon"></i>
                                                        <div class="search-addon d-none">
                                                            <span><i data-feather="x-circle"
                                                                    class="feather-14"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown-menu search-dropdown idea"
                                                        aria-labelledby="dropdownMenuClickable2">
                                                        <div class="search-info">
                                                            <p>Black </p>
                                                            <p>Red</p>
                                                            <p>Green</p>
                                                            <p>S</p>
                                                            <p>M</p>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-lg-6">
                                    <div class="modal-footer-btn popup">
                                        <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                            data-bs-dismiss="modal">Cancel</a>
                                        <a href="javascript:void(0);" class="btn btn-submit"
                                            data-bs-dismiss="modal">Create Attribute</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Adjustment -->

    <!-- Add Category -->
    <div class="modal fade" id="add-units-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add New Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->

    <!-- Add Brand -->
    <div class="modal fade" id="add-units-brand">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add New Brand</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Brand -->

    <!-- Add Unit -->
    <div class="modal fade" id="add-unit">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Unit</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Unit -->

    <!-- Add Variatent -->
    <div class="modal fade" id="add-variation">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Variation</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="modal-title-head people-cust-avatar">
                                <h6>Variant Thumbnail</h6>
                            </div>
                            <div class="new-employee-field">
                                <div class="profile-pic-upload">
                                    <div class="profile-pic">
                                        <span><i data-feather="plus-circle" class="plus-down-add"></i> Add
                                            Image</span>
                                    </div>
                                    <div class="mb-3">
                                        <div class="image-upload mb-0">
                                            <input type="file">
                                            <div class="image-uploads">
                                                <h4>Change Image</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Barcode Symbology</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Code34</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <div class="input-blocks add-product list">
                                            <label>Item Code</label>
                                            <input type="text" class="form-control list" value="455454478844">
                                            <button type="submit" class="btn btn-primaryadd">
                                                Generate Code
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks image-upload-down">
                                        <div class="image-upload download">
                                            <input type="file">
                                            <div class="image-uploads">
                                                <img src="{{ URL::asset('/build/img/download-img.png')}}" alt="img">
                                                <h4>Drag and drop a <span>file to upload</span></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-body">
                                        <div class="text-editor add-list add">
                                            <div class="col-lg-12">
                                                <div class="add-choosen mb-3">
                                                    <div class="phone-img ms-0">
                                                        <img src="{{ URL::asset('/build/img/products/phone-add-2.png')}}" alt="image">
                                                        <a href="javascript:void(0);"><i data-feather="x"
                                                                class="x-square-add remove-product"></i></a>
                                                    </div>

                                                    <div class="phone-img">
                                                        <img src="{{ URL::asset('/build/img/products/phone-add-1.png')}}" alt="image">
                                                        <a href="javascript:void(0);"><i data-feather="x"
                                                                class="x-square-add remove-product"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity Alert</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Tax Type</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Direct</option>
                                            <option>Indirect</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Tax </label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Income Tax</option>
                                            <option>Service Tax</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Discount Type </label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Percentage</option>
                                            <option>Early Payment</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 pe-0">
                                    <div>
                                        <label class="form-label">Discount Value</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>


                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Variatent -->
@endif

@if (Route::is(['expired-products']))
    <!-- Add PDF -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Export Report as PDF</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('expired-products') }}">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Choose Manufacturer Date</label>
                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker"
                                                    placeholder="Manufacturer Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Choose Expiry Date</label>
                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker"
                                                    placeholder="Expiry Date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Download Report</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add PDF -->
@endif

@if (Route::is(['low-stocks']))
    <!-- Send Mail -->
    <div class="modal fade" id="send-email">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="success-email-send modal-body .custom-modal-body text-center">
                    <span><i data-feather="check-circle" class="feather-trash-2"></i></span>
                    <h4>Success</h4>
                    <p>Email Sent Successfully</p>
                    <a href="" class="btn btn-primary" data-bs-dismiss="modal">Close</a>
                </div>
            </div>
        </div>
    </div>
    <!-- /Send Mail -->

    <!-- Edit Low Stock -->
    <div class="modal fade" id="edit-stock">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Edit Low Stocks</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('low-stocks') }}">
                                <div class="mb-3">
                                    <label class="form-label">Warehouse</label>
                                    <input type="text" class="form-control" value="Lavish Warehouse">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Store</label>
                                    <input type="text" class="form-control" value="Crinol">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" value="Laptop">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <input type="text" class="form-control" value="Lenevo 3rd Gen">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SKU</label>
                                    <input type="text" class="form-control" value="PT001">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty</label>
                                    <input type="text" class="form-control" value="15">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Alert</label>
                                    <input type="text" class="form-control" value="10">
                                </div>
                                <div class="mb-0">
                                    <div
                                        class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                        <span class="status-label">Status</span>
                                        <input type="checkbox" id="user3" class="check" checked="">
                                        <label for="user3" class="checktoggle"></label>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Edit Low Stock -->
@endif

@if (Route::is(['barcode']))
    <!-- Print Barcode -->
    <div class="modal fade" id="prints-barcode">
        <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Barcode</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-cancel close-btn">
                                    <span><i class="fas fa-print me-2"></i></span>
                                    Print Barcode</a>
                            </div>

                            <div class="barcode-scan-header">
                                <h5>Nike Jordan</h5>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="barcode-scanner-link text-center">
                                        <h6>Grocery Alpha</h6>
                                        <p>
                                            Nike Jordan
                                        </p>
                                        <p>Price: $400</p>
                                        <div class="barscaner-img">
                                            <img src="{{ URL::asset('/build/img/barcode/barcode-01.png')}}" alt="Barcode"
                                                class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="barcode-scan-header">
                                <h5>Apple Series 5 Watch</h5>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="barcode-scanner-link text-center">
                                        <h6>Grocery Alpha</h6>
                                        <p>
                                            Apple Series 5 Watch
                                        </p>
                                        <p>Price: $300</p>
                                        <div class="barscaner-img">
                                            <img src="{{ URL::asset('/build/img/barcode/barcode-02.png')}}" alt="Barcode"
                                                class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="barcode-scanner-link text-center">
                                        <h6>Grocery Alpha</h6>
                                        <p>
                                            Apple Series 5 Watch
                                        </p>
                                        <p>Price: $300</p>
                                        <div class="barscaner-img">
                                            <img src="{{ URL::asset('/build/img/barcode/barcode-02.png')}}" alt="Barcode"
                                                class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="barcode-scanner-link text-center">
                                        <h6>Grocery Alpha</h6>
                                        <p>
                                            Apple Series 5 Watch
                                        </p>
                                        <p>Price: $300</p>
                                        <div class="barscaner-img">
                                            <img src="{{ URL::asset('/build/img/barcode/barcode-02.png')}}" alt="Barcode"
                                                class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Print Barcode -->
@endif

@if (Route::is(['qrcode']))
    <!-- Print Qrcode -->
    <div class="modal fade" id="prints-barcode">
        <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>QR Codes</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-cancel close-btn">
                                    <span><i class="fas fa-print me-2"></i></span>
                                    Print QR Code</a>
                            </div>
                            <div class="barcode-scan-header">
                                <h5>Nike Jordan</h5>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="barcode-scanner-link text-center">
                                        <div class="barscaner-img">
                                            <img src="{{ URL::asset('/build/img/barcode/qr-code.png')}}" alt="Barcode"
                                                class="img-fluid">
                                        </div>
                                        <p>Ref No :32RRR554 </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Print Qrcode -->
@endif

@if (Route::is(['edit-product']))
    <!-- Add Adjustment -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Variation Attribute</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="input-blocks">
                                        <label>Attribute Name</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks">
                                        <label>Add Value</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <ul class="nav user-menu">
                                        <li class="nav-item nav-searchinputs">
                                            <div class="top-nav-search">
                                                <form action="#" class="dropdown">
                                                    <div class="searchinputs list dropdown-toggle"
                                                        id="dropdownMenuClickable2" data-bs-toggle="dropdown"
                                                        data-bs-auto-close="false">
                                                        <input type="text" placeholder="Search">
                                                        <i data-feather="search" class="feather-16 icon"></i>
                                                        <div class="search-addon d-none">
                                                            <span><i data-feather="x-circle"
                                                                    class="feather-14"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown-menu search-dropdown idea"
                                                        aria-labelledby="dropdownMenuClickable2">
                                                        <div class="search-info">
                                                            <p>Black </p>
                                                            <p>Red</p>
                                                            <p>Green</p>
                                                            <p>S</p>
                                                            <p>M</p>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-lg-6">
                                    <div class="modal-footer-btn popup">
                                        <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                            data-bs-dismiss="modal">Cancel</a>
                                        <a href="javascript:void(0);" class="btn btn-submit"
                                            data-bs-dismiss="modal">Create Attribute</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Adjustment -->

    <!-- Add Category -->
    <div class="modal fade" id="add-units-category">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add New Category</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Category -->

    <!-- Add Brand -->
    <div class="modal fade" id="add-units-brand">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add New Brand</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Brand -->

    <!-- Add Unit -->
    <div class="modal fade" id="add-unit">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Unit</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control">
                            </div>
                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Unit -->

    <!-- Add Variatent -->
    <div class="modal fade" id="add-variation">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Variation</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="modal-title-head people-cust-avatar">
                                <h6>Variant Thumbnail</h6>
                            </div>
                            <div class="new-employee-field">
                                <div class="profile-pic-upload">
                                    <div class="profile-pic">
                                        <span><i data-feather="plus-circle" class="plus-down-add"></i> Add
                                            Image</span>
                                    </div>
                                    <div class="mb-3">
                                        <div class="image-upload mb-0">
                                            <input type="file">
                                            <div class="image-uploads">
                                                <h4>Change Image</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Barcode Symbology</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Code34</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <div class="input-blocks add-product list">
                                            <label>Item Code</label>
                                            <input type="text" class="form-control list" value="455454478844">
                                            <button type="submit" class="btn btn-primaryadd">
                                                Generate Code
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks image-upload-down">
                                        <div class="image-upload download">
                                            <input type="file">
                                            <div class="image-uploads">
                                                <img src="{{ URL::asset('/build/img/download-img.png')}}" alt="img">
                                                <h4>Drag and drop a <span>file to upload</span></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-body">
                                        <div class="text-editor add-list add">
                                            <div class="col-lg-12">
                                                <div class="add-choosen mb-3">
                                                    <div class="phone-img ms-0">
                                                        <img src="{{ URL::asset('/build/img/products/phone-add-2.png')}}"
                                                            alt="image">
                                                        <a href="javascript:void(0);"><i data-feather="x"
                                                                class="x-square-add remove-product"></i></a>
                                                    </div>

                                                    <div class="phone-img">
                                                        <img src="{{ URL::asset('/build/img/products/phone-add-1.png')}}"
                                                            alt="image">
                                                        <a href="javascript:void(0);"><i data-feather="x"
                                                                class="x-square-add remove-product"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity Alert</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Tax Type</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Direct</option>
                                            <option>Indirect</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Tax </label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Income Tax</option>
                                            <option>Service Tax</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 pe-0">
                                    <div class="mb-3">
                                        <label class="form-label">Discount Type </label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Percentage</option>
                                            <option>Early Payment</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 pe-0">
                                    <div>
                                        <label class="form-label">Discount Value</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>


                            <div class="modal-footer-btn">
                                <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ url('add-product') }}" class="btn btn-submit">Submit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if (Route::is(['call-history']))
    <!-- details popup -->
    <div class="modal fade" id="user-profile-new">
        <div class="modal-dialog history-modal-profile">
            <div class="modal-content">
                <div class="page-wrapper details-blk">
                    <div class="content">
                        <div class="text-center right-sidebar-profile mb-3">
                            <figure class="avatar">
                                <img src="{{ URL::asset('/build/img/users/user-23.jpg')}}" alt="image">
                            </figure>
                            <div class="chat-options chat-option-profile">
                                <ul class="list-inline">
                                    <li class="list-inline-item">
                                        <a href="{{ url('audio-call') }}" class="btn btn-outline-light "
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                            data-bs-original-title="Voice Call">
                                            <i class="bx bx-phone"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="{{ url('chat') }}" class="btn btn-outline-light"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                            data-bs-original-title="Chat">
                                            <i class="bx bx-message-square-dots"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item ">
                                        <a href="{{ url('video-call') }}"
                                            class="btn btn-outline-light profile-open" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" title=""
                                            data-bs-original-title="Video Call">
                                            <i class="bx bx-video"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-profile-detail">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="modal-userlist">
                                        <ul>
                                            <li>Name<span>Thomas</span></li>
                                            <li>Phone<span>+1 25182 94528</span></li>
                                            <li>Email<span>thomas@example.com</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="modal-userlist d-flex justify-content-center">
                                        <ul>
                                            <li>Total Calls<span>20</span></li>
                                            <li>Average Call Timing<span>0.30</span></li>
                                            <li>Average Waiting Time<span>00.5</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /details popup -->
@endif

@if (Route::is(['todo']))
    <!-- Add Note -->
    <div class="modal fade" id="note-units">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Todo</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('todo') }}">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Todo Title</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Assignee</label>
                                            <select class="select">
                                                <option>Choose</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tag</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-blocks todo-calendar">
                                            <label class="form-label">Due Date</label>
                                            <div class="input-groupicon calender-input">
                                                <input type="text" class="form-control  date-range bookingrange"
                                                    placeholder="Select" value="13 Aug 1992">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3 summer-description-box notes-summernote">
                                            <label class="form-label">Descriptions</label>
                                            <div id="summernote"></div>
                                            <p>Maximum 60 Characters</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Note -->

    <!-- Edit Note -->
    <div class="modal fade" id="edit-note-units">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Todo Title</h4>
                            </div>
                            <div class=" edit-note-head d-flex align-items-center">
                                <a href="javascript:void(0);" class="me-2">
                                    <span>
                                        <i data-feather="trash-2"></i>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="me-2">
                                    <span>
                                        <i data-feather="star"></i>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="me-2">
                                    <span>
                                        <i data-feather="eye"></i>
                                    </span>
                                </a>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('todo') }}">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-blocks">
                                            <label class="form-label">Note Title</label>
                                            <input type="text" class="form-control"
                                                placeholder="Meet Lisa to discuss project details">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-blocks">
                                            <label class="form-label">Assignee</label>
                                            <select class="select">
                                                <option>Choose</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-blocks">
                                            <label class="form-label">Tag</label>
                                            <select class="select">
                                                <option>Onhold</option>
                                                <option>Onhold</option>
                                                <option>Onhold</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-blocks">
                                            <label class="form-label">Priority</label>
                                            <select class="select">
                                                <option>High</option>
                                                <option>Medium</option>
                                                <option>Low</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-blocks todo-calendar">
                                            <label class="form-label">Due Date</label>
                                            <div class="input-groupicon calender-input">
                                                <input type="text" class="form-control date-range bookingrange"
                                                    placeholder="Select" value="13 Aug 1992">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-blocks">
                                            <label class="form-label">Status</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="input-blocks summer-description-box notes-summernote">
                                            <label class="form-label">Descriptions</label>
                                            <div id="summernote2"></div>
                                            <p>Maximum 60 Characters</p>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Note -->

    <!-- Delete Note -->
    <div class="modal fade" id="delete-note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="delete-popup">
                            <div class="delete-image text-center mx-auto">
                                <img src="{{ URL::asset('/build/img/icons/close-circle.png')}}" alt="Img" class="img-fluid">
                            </div>
                            <div class="delete-heads">
                                <h4>Are You Sure?</h4>
                                <p>Do you really want to delete this item, This process cannot be undone.</p>
                            </div>
                            <div class="modal-footer-btn delete-footer">
                                <a href="" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</a>
                                <a href="" class="btn btn-submit">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Note -->

    <!-- View Note -->
    <div class="modal fade" id="view-note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title edit-page-title">
                                <h4>Todo</h4>
                                <p>Personal</p>
                            </div>
                            <div class=" edit-noted-head d-flex align-items-center">
                                <a href="javascript:void(0);">
                                    <span>
                                        <i data-feather="trash-2"></i>
                                    </span>
                                </a>
                                <a href="javascript:void(0);" class="me-2">
                                    <span>
                                        <i data-feather="star"></i>
                                    </span>
                                </a>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="edit-head-view">
                                        <h6>Meet Lisa to discuss project details</h6>
                                        <p>Hiking is a long, vigorous walk, usually on trails or footpaths in the
                                            countryside.
                                            Walking for pleasure developed in Europe during the eighteenth century.
                                            Religious pilgrimages have existed much longer but they involve walking long
                                            distances for a spiritual purpose associated with specific religions and
                                            also
                                            we achieve inner peace while we hike at a local park.</p>

                                        <p class="badged high"><i class="fas fa-circle"></i> High</p>
                                    </div>
                                    <div class="modal-footer-btn edit-footer-menu">
                                        <a href="" class="btn btn-cancel me-2"
                                            data-bs-dismiss="modal">Close</a>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /View Note -->
@endif

@if (Route::is(['notes']))
    <!-- Note Unit -->
    <div class="modal fade" id="note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add New Note</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('notes') }}">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Note Title</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">User</label>
                                            <select class="select">
                                                <option>Choose</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tag</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="select">
                                                <option>Select</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3 summer-description-box notes-summernote">
                                            <label class="form-label">Descriptions</label>
                                            <div id="summernote"></div>
                                            <p>Maximum 60 Characters</p>
                                        </div>
                                    </div>

                                </div>

                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Note Unit -->

    <!-- Note Unit -->
    <div class="modal fade" id="edit-note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Edit Note</h4>
                            </div>
                            <div class=" edit-note-head d-flex align-items-center">
                                <a href="#" class="me-2">
                                    <span>
                                        <i data-feather="trash-2"></i>
                                    </span>
                                </a>
                                <a href="#" class="me-2">
                                    <span>
                                        <i data-feather="star"></i>
                                    </span>
                                </a>
                                <a href="#" class="me-2">
                                    <span>
                                        <i data-feather="eye"></i>
                                    </span>
                                </a>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('notes') }}">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label>Note Title</label>
                                            <input type="text" class="form-control"
                                                placeholder="Meet Lisa to discuss project details">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Assignee</label>
                                            <select class="select">
                                                <option>Choose</option>
                                                <option>Recent1</option>
                                                <option>Recent2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tag</label>
                                            <select class="select">
                                                <option>Onhold</option>
                                                <option>Onhold</option>
                                                <option>Onhold</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="select">
                                                <option>High</option>
                                                <option>Medium</option>
                                                <option>Low</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3 summer-description-box notes-summernote">
                                            <label class="form-label">Descriptions</label>
                                            <div id="summernote2"></div>
                                            <p>Maximum 60 Characters</p>
                                        </div>
                                    </div>

                                </div>


                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Note Unit -->

    <!-- Delete Unit -->
    <div class="modal fade" id="delete-note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="delete-popup">
                            <div class="delete-image text-center mx-auto">
                                <img src="{{ URL::asset('/build/img/icons/close-circle.png')}}" alt="Img" class="img-fluid">
                            </div>
                            <div class="delete-heads">
                                <h4>Are You Sure?</h4>
                                <p>Do you really want to delete this item, This process cannot be undone.</p>
                            </div>
                            <div class="modal-footer-btn delete-footer">
                                <a href="" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</a>
                                <a href="" class="btn btn-submit">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Unit -->

    <!-- View Unit -->
    <div class="modal fade" id="view-note-units">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title edit-page-title">
                                <h4>Notes</h4>
                                <p>Personal</p>
                            </div>
                            <div class=" edit-noted-head d-flex align-items-center">
                                <a href="javascript:void(0);">
                                    <span>
                                        <i data-feather="trash-2"></i>
                                    </span>
                                </a>
                                <a href="#" class="me-2">
                                    <span>
                                        <i data-feather="star"></i>
                                    </span>
                                </a>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="{{ url('notes') }}">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="edit-head-view">
                                            <h6>Take a hike at a local park</h6>
                                            <p>Hiking is a long, vigorous walk, usually on trails or footpaths in the
                                                countryside.
                                                Walking for pleasure developed in Europe during the eighteenth century.
                                                Religious pilgrimages have existed much longer but they involve walking
                                                long
                                                distances for a spiritual purpose associated with specific religions and
                                                also
                                                we achieve inner peace while we hike at a local park.</p>

                                            <p class="badged low"><i class="fas fa-circle"></i> Low</p>
                                        </div>
                                        <div class="modal-footer-btn edit-footer-menu">
                                            <button type="button" class="btn btn-cancel me-2"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /View Unit -->
@endif

@if (Route::is(['file-shared']))
    <!-- Files Toogle Slide -->
    <div class="toggle-sidebar">
        <div class="d-flex align-items-center justify-content-between head">
            <h4>File Preview</h4>
            <div class="d-flex align-items-center">
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i
                        class="fa fa-star"></i></a>
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i data-feather="trash-2"
                        class="feather-16 text-center text-danger"></i></a>
                <a href="javascript:void(0);" class="sidebar-closes d-flex align-items-center"
                    aria-hidden="true"><i data-feather="x-circle" class="feather-26 color-primary"></i></a>
            </div>
        </div>
        <div class="text-center">
            <a href="javascript:void(0);"><img src="{{ URL::asset('/build/img/file-manager/folder-lg.png')}}" alt="Folder"></a>
            <h5>Website Backup for the Design team</h5>
            <p>File Size : 616 MB</p>
        </div>

        <div class="nav nav-tabs d-flex align-items-center justify-content-between py-4 mb-4" id="nav-tab"
            role="tablist">
            <a class="nav-link flex-fill active btn btn-light me-2 text-center" id="nav-home-tab"
                data-bs-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home"
                aria-selected="true"><i data-feather="list" class="feather-16 me-2 text-center"></i>Details</a>
            <a class="nav-link flex-fill btn btn-light" id="nav-profile-tab" data-bs-toggle="tab"
                href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><i
                    data-feather="clock" class="feather-16 me-2"></i>Activity</a>
        </div>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                aria-labelledby="nav-home-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="edit"
                        class="feather-20 me-2"></i>Properties</h5>
                <ul class="seprator-lg">
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>Website Backup for the Designteam</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Type</h6>
                        <p>Folder</p>
                    </li>
                    <li class="mb-4">
                        <h6>Size</h6>
                        <p>616 MB</p>
                    </li>
                    <li class="mb-4">
                        <h6>Created</h6>
                        <p>22 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Location</h6>
                        <p class="location d-inline-flex align-items-center"><i data-feather="hard-drive"
                                class="feather-16 me-1"></i>Drive</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>23 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Opened On</h6>
                        <p>28 July 2023, 06:40 PM</p>
                    </li>
                    <li>
                        <div class="row">
                            <!-- Editor -->
                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box transfer">
                                    <label>Description</label>
                                    <div id="summernote3">
                                    </div>
                                    <p>Maximum 60 Characters</p>
                                </div>
                            </div>
                            <!-- /Editor -->
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="user" class="feather-20 me-2"></i>Who
                    has access</h5>
                <div class="d-flex align-items-center justify-content-between avatar-wrap">
                    <div class="avatar-access d-flex align-items-center mb-4">
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 1" data-bs-original-title="Member 1"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 2" data-bs-original-title="Member 2"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 3" data-bs-original-title="Member 3"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 4" data-bs-original-title="Member 4"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);"
                                class="avatar-md add d-flex align-items-center justify-content-center"><i
                                    data-feather="plus" class="feather-16 me-1"></i></a>
                        </span>
                    </div>
                </div>
                <p>Owned by Andrew. Shared with James, Fin, Davis</p>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>This Week</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>Last Month</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <a href="javascript:void(0);" class="text-primary show-all"><i data-feather="plus-circle"
                        class="feather-20 me-2"></i>Show All</a>
            </div>
        </div>

    </div>
    <!-- Files Toogle Slide -->

    <!-- Upload File -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-file" aria-labelledby="upload-file">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 1 files Uploaded</p>
                        <span>70%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 75%"
                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version.zip<i
                                                data-feather="check-circle" class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Updated Project.zip</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="0"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
    <!-- /Upload File -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-folder"
        aria-labelledby="upload-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version<i data-feather="check-circle"
                                                class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls<i
                                                data-feather="trash-2" class="feather-16"></i></a></h6>
                                    <span>16 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">updated project.zip<i data-feather="trash-2"
                                                class="feather-16"></i></a></h6>
                                    <span>14 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-message" id="upload-message"
        aria-labelledby="upload-message">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Create Folder -->
    <div class="modal fade modal-default pos-modal" id="create-folder" aria-labelledby="create-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Create Folder</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Folder Name</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer d-sm-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Create Folder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Create Folder -->
@endif

@if (Route::is(['file-document', 'file-favourites', 'file-archived']))
    <!-- Files Toogle Slide -->
    <div class="toggle-sidebar">
        <div class="d-flex align-items-center justify-content-between head">
            <h4>File Preview</h4>
            <div class="d-flex align-items-center">
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i
                        class="fa fa-star"></i></a>
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i data-feather="trash-2"
                        class="feather-16 text-center text-danger"></i></a>
                <a href="javascript:void(0);" class="sidebar-closes d-flex align-items-center"
                    aria-hidden="true"><i data-feather="x-circle" class="feather-26 color-primary"></i></a>
            </div>
        </div>
        <div class="text-center">
            <a href="javascript:void(0);"><img src="{{ URL::asset('/build/img/file-manager/folder-lg.png')}}" alt="Folder"></a>
            <h5>Website Backup for the Design team</h5>
            <p>File Size : 616 MB</p>
        </div>

        <div class="nav nav-tabs d-flex align-items-center justify-content-between py-4 mb-4" id="nav-tab"
            role="tablist">
            <a class="nav-link flex-fill active btn btn-light me-2 text-center" id="nav-home-tab"
                data-bs-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home"
                aria-selected="true"><i data-feather="list" class="feather-16 me-2 text-center"></i>Details</a>
            <a class="nav-link flex-fill btn btn-light" id="nav-profile-tab" data-bs-toggle="tab"
                href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><i
                    data-feather="clock" class="feather-16 me-2"></i>Activity</a>
        </div>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                aria-labelledby="nav-home-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="edit"
                        class="feather-20 me-2"></i>Properties</h5>
                <ul class="seprator-lg">
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>Website Backup for the Designteam</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Type</h6>
                        <p>Folder</p>
                    </li>
                    <li class="mb-4">
                        <h6>Size</h6>
                        <p>616 MB</p>
                    </li>
                    <li class="mb-4">
                        <h6>Created</h6>
                        <p>22 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Location</h6>
                        <p class="location d-inline-flex align-items-center"><i data-feather="hard-drive"
                                class="feather-16 me-1"></i>Drive</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>23 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Opened On</h6>
                        <p>28 July 2023, 06:40 PM</p>
                    </li>
                    <li>
                        <div class="row">
                            <!-- Editor -->
                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box transfer">
                                    <label>Description</label>
                                    <div id="summernote3">
                                    </div>
                                    <p>Maximum 60 Characters</p>
                                </div>
                            </div>
                            <!-- /Editor -->
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="user" class="feather-20 me-2"></i>Who
                    has access</h5>
                <div class="d-flex align-items-center justify-content-between avatar-wrap">
                    <div class="avatar-access d-flex align-items-center mb-4">
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 1" data-bs-original-title="Member 1"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 2" data-bs-original-title="Member 2"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 3" data-bs-original-title="Member 3"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 4" data-bs-original-title="Member 4"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);"
                                class="avatar-md add d-flex align-items-center justify-content-center"><i
                                    data-feather="plus" class="feather-16 me-1"></i></a>
                        </span>
                    </div>
                </div>
                <p>Owned by Andrew. Shared with James, Fin, Davis</p>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>This Week</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>Last Month</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <a href="javascript:void(0);" class="text-primary show-all"><i data-feather="plus-circle"
                        class="feather-20 me-2"></i>Show All</a>
            </div>
        </div>

    </div>
    <!-- Files Toogle Slide -->

    <!-- Upload File -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-file" aria-labelledby="upload-file">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 1 files Uploaded</p>
                        <span>70%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 75%"
                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version.zip<i
                                                data-feather="check-circle" class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Updated Project.zip</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="0"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
    <!-- /Upload File -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-folder"
        aria-labelledby="upload-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version<i data-feather="check-circle"
                                                class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls<i
                                                data-feather="trash-2" class="feather-16"></i></a></h6>
                                    <span>16 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">updated project.zip<i data-feather="trash-2"
                                                class="feather-16"></i></a></h6>
                                    <span>14 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-message" id="upload-message"
        aria-labelledby="upload-message">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Create Folder -->
    <div class="modal fade modal-default pos-modal" id="create-folder" aria-labelledby="create-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Create Folder</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Folder Name</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer d-sm-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Create Folder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Create Folder -->
@endif

@if (Route::is(['file-recent']))
    <!-- Files Toogle Slide -->
    <div class="toggle-sidebar">
        <div class="d-flex align-items-center justify-content-between head">
            <h4>File Preview</h4>
            <div class="d-flex align-items-center">
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i
                        class="fa fa-star"></i></a>
                <a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i data-feather="trash-2"
                        class="feather-16 text-center text-danger"></i></a>
                <a href="javascript:void(0);" class="sidebar-closes d-flex align-items-center"
                    aria-hidden="true"><i data-feather="x-circle" class="feather-26 color-primary"></i></a>
            </div>
        </div>
        <div class="text-center">
            <a href="javascript:void(0);"><img src="{{ URL::asset('/build/img/file-manager/folder-lg.png')}}" alt="Folder"></a>
            <h5>Website Backup for the Design team</h5>
            <p>File Size : 616 MB</p>
        </div>

        <div class="nav nav-tabs d-flex align-items-center justify-content-between py-4 mb-4" id="nav-tab"
            role="tablist">
            <a class="nav-link flex-fill active btn btn-light me-2 text-center" id="nav-home-tab"
                data-bs-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home"
                aria-selected="true"><i data-feather="list" class="feather-16 me-2 text-center"></i>Details</a>
            <a class="nav-link flex-fill btn btn-light" id="nav-profile-tab" data-bs-toggle="tab"
                href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><i
                    data-feather="clock" class="feather-16 me-2"></i>Activity</a>
        </div>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                aria-labelledby="nav-home-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="edit"
                        class="feather-20 me-2"></i>Properties</h5>
                <ul class="seprator-lg">
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>Website Backup for the Designteam</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Type</h6>
                        <p>Folder</p>
                    </li>
                    <li class="mb-4">
                        <h6>Size</h6>
                        <p>616 MB</p>
                    </li>
                    <li class="mb-4">
                        <h6>Created</h6>
                        <p>22 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Location</h6>
                        <p class="location d-inline-flex align-items-center"><i data-feather="hard-drive"
                                class="feather-16 me-1"></i>Drive</p>
                    </li>
                    <li class="mb-4">
                        <h6>File Name</h6>
                        <p>23 July 2023, 08:30 PM</p>
                    </li>
                    <li class="mb-4">
                        <h6>Opened On</h6>
                        <p>28 July 2023, 06:40 PM</p>
                    </li>
                    <li>
                        <div class="row">
                            <!-- Editor -->
                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box transfer">
                                    <label>Description</label>
                                    <div id="summernote3">
                                    </div>
                                    <p>Maximum 60 Characters</p>
                                </div>
                            </div>
                            <!-- /Editor -->
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="user" class="feather-20 me-2"></i>Who
                    has access</h5>
                <div class="d-flex align-items-center justify-content-between avatar-wrap">
                    <div class="avatar-access d-flex align-items-center mb-4">
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 1" data-bs-original-title="Member 1"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 2" data-bs-original-title="Member 2"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 3" data-bs-original-title="Member 3"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right"
                                aria-label="Member 4" data-bs-original-title="Member 4"><img
                                    src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
                        </span>
                        <span>
                            <a href="javascript:void(0);"
                                class="avatar-md add d-flex align-items-center justify-content-center"><i
                                    data-feather="plus" class="feather-16 me-1"></i></a>
                        </span>
                    </div>
                </div>
                <p>Owned by Andrew. Shared with James, Fin, Davis</p>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>This Week</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar"
                        class="feather-20 me-2"></i>Last Month</h5>
                <ul class="mb-4">
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <p>Drake shared an item<br>3:39 PM Jul 19</p>
                        </div>
                        <p class="d-flex align-items-center location border-0"><img
                                src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for
                            the Design team</p>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Melvin</p>
                                <p class="mb-0">Commentor</p>
                            </div>
                        </div>
                    </li>
                    <li class="mb-2">
                        <div class="d-flex align-items-center mb-2">
                            <a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}"
                                    alt="Avatar" class="avatar-md"></a>
                            <div>
                                <p class="mb-0 text-secondary">Drake</p>
                                <p class="mb-0">Editor</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <a href="javascript:void(0);" class="text-primary show-all"><i data-feather="plus-circle"
                        class="feather-20 me-2"></i>Show All</a>
            </div>
        </div>

    </div>
    <!-- Files Toogle Slide -->

    <!-- Upload File -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-file" aria-labelledby="upload-file">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 1 files Uploaded</p>
                        <span>70%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 75%"
                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version.zip<i
                                                data-feather="check-circle" class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 75%"
                                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Updated Project.zip</a></h6>
                                    <span>616 MB</span>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="0"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
    <!-- /Upload File -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-modal" id="upload-folder"
        aria-labelledby="upload-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="drag-drop text-center mb-4">
                        <div class="upload">
                            <a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
                            <p>Drag and drop a <a href="#">file to upload</a></p>
                        </div>
                        <input type="file" multiple="">
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <ul>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">latest-version<i data-feather="check-circle"
                                                class="ms-2 feather-16"></i></a></h6>
                                    <span>616 MB</span>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2"
                                    class="feather-16"></i></a>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">Update work history.xls<i
                                                data-feather="trash-2" class="feather-16"></i></a></h6>
                                    <span>16 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                        <li class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center w-85">
                                <img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
                                <div class="flex-fill">
                                    <h6><a href="javascript:void(0);">updated project.zip<i data-feather="trash-2"
                                                class="feather-16"></i></a></h6>
                                    <span>14 MB</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2"
                                        class="feather-16"></i></a>
                                <a href="javascript:void(0);" class="text-default"><i data-feather="play-circle"
                                        class="feather-16"></i></a>
                            </div>
                        </li>
                    </ul>

                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Upload Folder -->
    <div class="modal fade modal-default pos-modal upload-message" id="upload-message"
        aria-labelledby="upload-message">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Upload File</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <div class="d-flex align-items-center justify-content-between">
                        <p>3 of 3 files Uploaded</p>
                        <span>100%</span>
                    </div>
                    <div class="progress mt-2 mb-4">
                        <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%"
                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="modal-footer d-sm-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
                    <button type="button" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Upload Folder -->

    <!-- Create Folder -->
    <div class="modal fade modal-default pos-modal" id="create-folder" aria-labelledby="create-folder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Create Folder</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Folder Name</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer d-sm-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Create Folder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Create Folder -->
@endif

@if (Route::is(['chat']))
    <!-- Add Transfer -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog purchase modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Transfer</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-blocks">
                                        <label>Date</label>

                                        <div class="input-groupicon calender-input">
                                            <i data-feather="calendar" class="info-img"></i>
                                            <input type="text" class="datetimepicker form-control"
                                                placeholder="Select Date">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-blocks">
                                        <label>From</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Store 1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-blocks">
                                        <label>To</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Store 2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="input-blocks">
                                        <label>Product Name</label>
                                        <input type="text" placeholder="Please type product code and select">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="modal-body-table">
                                        <div class="table-responsive">
                                            <table class="table  datanew">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Qty</th>
                                                        <th>Purchase Price($)</th>
                                                        <th>Discount($)</th>
                                                        <th>Tax(%)</th>
                                                        <th>Tax Amount($)</th>
                                                        <th>Unit Cost($)</th>
                                                        <th>Total Cost(%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                        <td class="p-5"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Order Tax</label>
                                            <input type="text" value="0">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Discount</label>
                                            <input type="text" value="0">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Shipping</label>
                                            <input type="text" value="0">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Status</label>
                                            <select class="select">
                                                <option>Choose</option>
                                                <option>Sent</option>
                                                <option>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box">
                                    <label>Notes</label>
                                    <div id="summernote"></div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="modal-footer-btn">
                                    <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</a>
                                    <a href="javascript:void(0);" class="btn btn-submit">Submit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Transfer -->

    <!-- Edit Transfer -->
    <div class="modal fade" id="edit-units">
        <div class="modal-dialog purchase modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Edit Transfer</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div>
                                <div>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="input-blocks">
                                                <label>Date</label>

                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="19 Jan 2023">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="input-blocks">
                                                <label>From</label>
                                                <select class="select">
                                                    <option>Store 1</option>
                                                    <option>Choose</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="input-blocks">
                                                <label>To</label>
                                                <select class="select">
                                                    <option>Store 2</option>
                                                    <option>Choose</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product</label>
                                                <div class="input-groupicon">
                                                    <input type="text"
                                                        placeholder="Scan/Search Product by code and select...">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/scanners.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="modal-body-table total-orders">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Product Name</th>
                                                                <th>QTY</th>
                                                                <th>Purchase Price($) </th>
                                                                <th>Discount($) </th>
                                                                <th>Tax %</th>
                                                                <th>Tax Amount($)</th>
                                                                <th class="text-end">Unit Cost($)</th>
                                                                <th class="text-end">Total Cost ($) </th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <div class="productimgname">
                                                                        <a href="javascript:void(0);"
                                                                            class="product-img stock-img">
                                                                            <img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}"
                                                                                alt="product">
                                                                        </a>
                                                                        <a href="javascript:void(0);">Nike Jordan</a>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="product-quantity">
                                                                        <span class="quantity-btn">+<i
                                                                                data-feather="plus-circle"
                                                                                class="plus-circle"></i></span>
                                                                        <input type="text" class="quntity-input"
                                                                            value="10">
                                                                        <span class="quantity-btn"><i
                                                                                data-feather="minus-circle"
                                                                                class="feather-search"></i></span>
                                                                    </div>
                                                                </td>
                                                                <td>2000</td>
                                                                <td>500.00</td>
                                                                <td>0.00</td>
                                                                <td>0.00</td>
                                                                <td class="text-end">0.00</td>
                                                                <td class="text-end">1500</td>
                                                                <td>
                                                                    <a class="delete-set"><img
                                                                            src="{{ URL::asset('/build/img/icons/delete.svg')}}"
                                                                            alt="svg')}}"></a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 float-md-right">
                                            <div class="total-order">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li class="total">
                                                        <h4>Grand Total</h4>
                                                        <h5>$1500.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Order Tax</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Discount</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Shipping</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Sent</option>
                                                    <option>Pending</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box">
                                    <label>Description</label>
                                    <div id="summernote2">
                                        <p>These shoes are made with the highest quality materials. </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="modal-footer-btn">
                                    <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</a>
                                    <a href="javascript:void(0);" class="btn btn-submit">Save Changes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Transfer -->

    <!-- Import Purchase -->
    <div class="modal fade" id="view-notes">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Import Transfer</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <label>From</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Store 1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <label>To</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Store 2</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <label>Satus</label>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Sent</option>
                                            <option>Pending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-6 col-12">
                                    <div class="row">
                                        <div>
                                            <!-- <div class="input-blocks download">
            <a class="btn btn-submit">Download Sample File</a>
           </div> -->
                                            <div class="modal-footer-btn download-file">
                                                <a href="javascript:void(0)" class="btn btn-submit">Download Sample
                                                    File</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks image-upload-down">
                                        <label> Upload CSV File</label>
                                        <div class="image-upload download">
                                            <input type="file">
                                            <div class="image-uploads">
                                                <img src="{{ URL::asset('/build/img/download-img.png')}}" alt="img">
                                                <h4>Drag and drop a <span>file to upload</span></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <label>Shipping</label>
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box transfer">
                                    <label>Description</label>
                                    <div id="summernote3">
                                    </div>
                                    <p>Maximum 60 Characters</p>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="modal-footer-btn">
                                    <a href="javascript:void(0);" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</a>
                                    <a href="{{ url('purchase-list') }}" class="btn btn-submit">Submit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Import Purchase -->
@endif

@if (Route::is(['sales-list']))
    <!--add popup -->
    <div class="modal fade" id="add-sales-new">
        <div class="modal-dialog add-centered">
            <div class="modal-content">
                <div class="page-wrapper p-0 m-0">
                    <div class="content p-0">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4> Add Sales</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="sales-list">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Customer Name</label>
                                                <div class="row">
                                                    <div class="col-lg-10 col-sm-10 col-10">
                                                        <select class="select">
                                                            <option>Choose</option>
                                                            <option>Customer Name</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                        <div class="add-icon">
                                                            <a href="#" class="choose-add"><i
                                                                    data-feather="plus-circle"
                                                                    class="plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Date</label>
                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="Choose">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Supplier</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                    <option>Supplier Name</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product Name</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text"
                                                        placeholder="Please type product code and select">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/qrcode-scan.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive no-pagination">
                                        <table class="table  datanew">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th>Purchase Price($)</th>
                                                    <th>Discount($)</th>
                                                    <th>Tax(%)</th>
                                                    <th>Tax Amount($)</th>
                                                    <th>Unit Cost($)</th>
                                                    <th>Total Cost(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 ms-auto">
                                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Grand Total</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Order Tax</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Discount</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Shipping</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-5">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                    <option>Completed</option>
                                                    <option>Inprogress</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button type="button" class="btn btn-cancel add-cancel me-3"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-submit add-sale">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /add popup -->

    <!-- details popup -->
    <div class="modal fade" id="sales-details-new">
        <div class="modal-dialog sales-details-modal">
            <div class="modal-content">
                <div class="page-wrapper details-blk">
                    <div class="content p-0">
                        <div class="page-header p-4 mb-0">
                            <div class="add-item d-flex">
                                <div class="page-title modal-datail">
                                    <h4>Sales Detail : SL0101</h4>
                                </div>
                                <div class="page-btn">
                                    <a href="#" class="btn btn-added"><i data-feather="plus-circle"
                                            class="me-2"></i> Add New Sales</a>
                                </div>
                            </div>
                            <ul class="table-top-head">
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><i
                                            data-feather="edit" class="action-edit sales-action"></i></a>
                                </li>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img
                                            src="{{ URL::asset('/build/img/icons/pdf.svg')}}" alt="img"></a>
                                </li>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img
                                            src="{{ URL::asset('/build/img/icons/excel.svg')}}" alt="img"></a>
                                </li>
                                <li>
                                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i
                                            data-feather="printer" class="feather-rotate-ccw"></i></a>
                                </li>
                            </ul>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <form action="sales-list">
                                    <div class="invoice-box table-height"
                                        style="max-width: 1600px;width:100%;overflow: auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                                        <div class="sales-details-items d-flex">
                                            <div class="details-item">
                                                <h6>Customer Info</h6>
                                                <p>walk-in-customer<br>
                                                    walk-in-customer@example.com<br>
                                                    123456780<br>
                                                    N45 , Dhaka
                                                </p>
                                            </div>
                                            <div class="details-item">
                                                <h6>Company Info</h6>
                                                <p>DGT<br>
                                                    admin@example.com<br>
                                                    6315996770<br>
                                                    3618 Abia Martin Drive
                                                </p>
                                            </div>
                                            <div class="details-item">
                                                <h6>Invoice Info</h6>
                                                <p>Reference<br>
                                                    Payment Status<br>
                                                    Status
                                                </p>
                                            </div>
                                            <div class="details-item">
                                                <h5><span>SL0101</span>Paid<br> Completed</h5>
                                            </div>
                                        </div>
                                        <h5 class="order-text">Order Summary</h5>
                                        <div class="table-responsive no-pagination">
                                            <table class="table  datanew">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Qty</th>
                                                        <th>Purchase Price($)</th>
                                                        <th>Discount($)</th>
                                                        <th>Tax(%)</th>
                                                        <th>Tax Amount($)</th>
                                                        <th>Unit Cost($)</th>
                                                        <th>Total Cost(%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class="productimgname">
                                                                <a href="javascript:void(0);"
                                                                    class="product-img stock-img">
                                                                    <img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}"
                                                                        alt="product">
                                                                </a>
                                                                <a href="javascript:void(0);">Nike Jordan</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="product-quantity">
                                                                <span class="quantity-btn">+<i
                                                                        data-feather="plus-circle"
                                                                        class="plus-circle"></i></span>
                                                                <input type="text" class="quntity-input"
                                                                    value="2">
                                                                <span class="quantity-btn"><i
                                                                        data-feather="minus-circle"
                                                                        class="feather-search"></i></span>
                                                            </div>
                                                        </td>
                                                        <td>2000</td>
                                                        <td>500</td>
                                                        <td>
                                                            0.00
                                                        </td>
                                                        <td>0.00</td>
                                                        <td>0.00</td>
                                                        <td>1500</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="productimgname">
                                                                <a href="javascript:void(0);"
                                                                    class="product-img stock-img">
                                                                    <img src="{{ URL::asset('/build/img/products/stock-img-03.png')}}"
                                                                        alt="product">
                                                                </a>
                                                                <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="product-quantity">
                                                                <span class="quantity-btn">+<i
                                                                        data-feather="plus-circle"
                                                                        class="plus-circle"></i></span>
                                                                <input type="text" class="quntity-input"
                                                                    value="2">
                                                                <span class="quantity-btn"><i
                                                                        data-feather="minus-circle"
                                                                        class="feather-search"></i></span>
                                                            </div>
                                                        </td>
                                                        <td>3000</td>
                                                        <td>400</td>
                                                        <td>
                                                            0.00
                                                        </td>
                                                        <td>0.00</td>
                                                        <td>0.00</td>
                                                        <td>1700</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="productimgname">
                                                                <a href="javascript:void(0);"
                                                                    class="product-img stock-img">
                                                                    <img src="{{ URL::asset('/build/img/products/stock-img-05.png')}}"
                                                                        alt="product">
                                                                </a>
                                                                <a href="javascript:void(0);">Lobar Handy</a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="product-quantity">
                                                                <span class="quantity-btn">+<i
                                                                        data-feather="plus-circle"
                                                                        class="plus-circle"></i></span>
                                                                <input type="text" class="quntity-input"
                                                                    value="2">
                                                                <span class="quantity-btn"><i
                                                                        data-feather="minus-circle"
                                                                        class="feather-search"></i></span>
                                                            </div>
                                                        </td>
                                                        <td>2500</td>
                                                        <td>500</td>
                                                        <td>
                                                            0.00
                                                        </td>
                                                        <td>0.00</td>
                                                        <td>0.00</td>
                                                        <td>2000</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="row">
                                            <div class="col-lg-6 ms-auto">
                                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                    <ul>
                                                        <li>
                                                            <h4>Order Tax</h4>
                                                            <h5>$ 0.00</h5>
                                                        </li>
                                                        <li>
                                                            <h4>Discount</h4>
                                                            <h5>$ 0.00</h5>
                                                        </li>
                                                        <li>
                                                            <h4>Grand Total</h4>
                                                            <h5>$ 5200.00</h5>
                                                        </li>
                                                        <li>
                                                            <h4>Paid</h4>
                                                            <h5>$ 5200.00</h5>
                                                        </li>
                                                        <li>
                                                            <h4>Due</h4>
                                                            <h5>$ 0.00</h5>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /details popup -->

    <!-- edit popup -->
    <div class="modal fade" id="edit-sales-new">
        <div class="modal-dialog edit-sales-modal">
            <div class="modal-content">
                <div class="page-wrapper p-0 m-0">
                    <div class="content p-0">
                        <div class="page-header p-4 mb-0">
                            <div class="add-item new-sale-items d-flex">
                                <div class="page-title">
                                    <h4>Edit Sales</h4>
                                </div>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="sales-list">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Customer</label>
                                                <div class="row">
                                                    <div class="col-lg-10 col-sm-10 col-10">
                                                        <select class="select">
                                                            <option>Thomas</option>
                                                            <option>Name</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                        <div class="add-icon">
                                                            <a href="#" class="choose-add"><i
                                                                    data-feather="plus-circle"
                                                                    class="plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Purchase Date</label>
                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="19 jan 2023">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Supplier</label>
                                                <select class="select">
                                                    <option>Dazzle Shoes</option>
                                                    <option>Supplier Name</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product Name</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text"
                                                        placeholder="Please type product code and select">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/scanners.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive no-pagination">
                                        <table class="table  datanew">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th>Purchase Price($)</th>
                                                    <th>Discount($)</th>
                                                    <th>Tax(%)</th>
                                                    <th>Tax Amount($)</th>
                                                    <th>Unit Cost($)</th>
                                                    <th>Total Cost(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Nike Jordan</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>2000</td>
                                                    <td>500</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>1500</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-03.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>3000</td>
                                                    <td>400</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>1700</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-05.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Lobar Handy</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>2500</td>
                                                    <td>500</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>2000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 ms-auto">
                                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Grand Total</h4>
                                                        <h5>$5200.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Order Tax</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Discount</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Shipping</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-5">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                    <option>Completed</option>
                                                    <option>Inprogress</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="input-blocks">
                                                <label>Notes</label>
                                                <textarea class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button type="button" class="btn btn-cancel add-cancel me-3"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-submit add-sale">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /edit popup -->

    <!-- show payment Modal -->
    <div class="modal fade" id="showpayment" tabindex="-1" aria-labelledby="showpayment" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Show Payments</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="modal-body-table total-orders">
                                        <div class="table-responsive">
                                            <table class="table  datanew">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Reference</th>
                                                        <th>Amount</th>
                                                        <th>Paid By</th>
                                                        <th class="no-sort">Action</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>19 Jan 2023</td>
                                                        <td>INV/SL0101</td>
                                                        <td>$1500</td>
                                                        <td>Cash</td>
                                                        <td class="action-table-data">
                                                            <div class="edit-delete-action">
                                                                <a class="me-3 p-2" href="javascript:void(0);">
                                                                    <i data-feather="printer"
                                                                        class="feather-rotate-ccw"></i>
                                                                </a>
                                                                <a class="me-3 p-2" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editpayment">
                                                                    <i data-feather="edit" class="feather-edit"></i>
                                                                </a>
                                                                <a class="confirm-text p-2"
                                                                    href="javascript:void(0);">
                                                                    <i data-feather="trash-2"
                                                                        class="feather-trash-2"></i>
                                                                </a>
                                                            </div>

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- show payment Modal -->

    <!-- Create payment Modal -->
    <div class="modal fade" id="createpayment" tabindex="-1" aria-labelledby="createpayment"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 custom-modal-header">
                    <div class="page-title">
                        <h4>Create Payments</h4>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body custom-modal-body">
                    <form action="sales-list">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-blocks">
                                    <label> Date</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="calendar" class="info-img"></i>
                                        <input type="text" class="datetimepicker" placeholder="Choose Date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Reference</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Received Amount</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="dollar-sign" class="info-img"></i>
                                        <input type="text">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Paying Amount</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="dollar-sign" class="info-img"></i>
                                        <input type="text">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Payment type</label>
                                    <select class="select">
                                        <option>Choose</option>
                                        <option>Online</option>
                                        <option>Cash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="input-blocks">
                                    <label>Description</label>
                                    <textarea class="form-control"></textarea>
                                    <p>Maximum 60 Characters</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="modal-footer-btn">
                                <button type="button" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Create payment Modal -->

    <!-- edit payment Modal -->
    <div class="modal fade" id="editpayment" tabindex="-1" aria-labelledby="editpayment" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 custom-modal-header">
                    <div class="page-title">
                        <h4>Edit Payments</h4>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body custom-modal-body">
                    <form action="sales-list">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-blocks">
                                    <label>19 Jan 2023</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="calendar" class="info-img"></i>
                                        <input type="text" class="datetimepicker form-control"
                                            placeholder="Select Date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Reference</label>
                                    <input type="text" value="INV/SL0101">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Received Amount</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="dollar-sign" class="info-img"></i>
                                        <input type="text" value="1500">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Paying Amount</label>
                                    <div class="input-groupicon calender-input">
                                        <i data-feather="dollar-sign" class="info-img"></i>
                                        <input type="text" value="1500">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Payment type</label>
                                    <select class="select">
                                        <option>Cash</option>
                                        <option>Online</option>
                                        <option>Inprogress</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="input-blocks summer-description-box transfer">
                                    <label>Description</label>
                                    <textarea class="form-control"></textarea>
                                </div>
                                <p>Maximum 60 Characters</p>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="modal-footer-btn mb-3 me-3">
                                <button type="button" class="btn btn-cancel me-2"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-submit">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- edit payment Modal -->
@endif

@if (Route::is(['sales-returns']))
    <!-- add popup -->
    <div class="modal fade" id="add-sales-new">
        <div class="modal-dialog add-centered">
            <div class="modal-content">
                <div class="page-wrapper p-0 m-0">
                    <div class="content p-0">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4> Add Sales Return</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="sales-returns">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label class="form-label">Customer Name</label>
                                                <div class="row">
                                                    <div class="col-lg-10 col-sm-10 col-10">
                                                        <select class="select">
                                                            <option>Choose Customer</option>
                                                            <option>Thomas</option>
                                                            <option>Benjamin</option>
                                                            <option>Bruklin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                        <div class="add-icon">
                                                            <a href="#" class="choose-add"><i
                                                                    data-feather="plus-circle"
                                                                    class="plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Date</label>
                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="Choose">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label class="form-label">Reference No.</label>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product Name</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text"
                                                        placeholder="Please type product code and select">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/qrcode-scan.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive no-pagination">
                                        <table class="table  datanew">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Net Unit Price($) </th>
                                                    <th>Stock</th>
                                                    <th>QTY </th>
                                                    <th>Discount($) </th>
                                                    <th>Tax %</th>
                                                    <th>Subtotal ($)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 ms-auto">
                                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Grand Total</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Order Tax</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Discount</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Shipping</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-5">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                    <option>Pending</option>
                                                    <option>Received</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button type="button" class="btn btn-cancel add-cancel me-3"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-submit add-sale">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /add popup -->

    <!-- Edit popup -->
    <div class="modal fade" id="edit-sales-new">
        <div class="modal-dialog add-centered">
            <div class="modal-content">
                <div class="page-wrapper p-0 m-0">
                    <div class="content p-0">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4> Add Sales Return</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="sales-returns">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label class="form-label">Customer Name</label>
                                                <div class="row">
                                                    <div class="col-lg-10 col-sm-10 col-10">
                                                        <select class="select">
                                                            <option>Thomas</option>
                                                            <option>Benjamin</option>
                                                            <option>Bruklin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                        <div class="add-icon">
                                                            <a href="#" class="choose-add"><i
                                                                    data-feather="plus-circle"
                                                                    class="plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Date</label>
                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="Choose">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label class="form-label">Reference No.</label>
                                                <input type="text" class="form-control" value="555444">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product Name</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text"
                                                        placeholder="Please type product code and select">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/qrcode-scan.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive no-pagination">
                                        <table class="table  datanew">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Net Unit Price($) </th>
                                                    <th>Stock</th>
                                                    <th>QTY </th>
                                                    <th>Discount($) </th>
                                                    <th>Tax %</th>
                                                    <th>Subtotal ($)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);" class="product-img">
                                                                <img src="{{ URL::asset('/build/img/products/product6.jpg')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Apple Earpods</a>
                                                        </div>
                                                    </td>
                                                    <td>300</td>
                                                    <td>400</td>
                                                    <td>500</td>
                                                    <td>100</td>
                                                    <td>50</td>
                                                    <td>300</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);" class="product-img">
                                                                <img src="{{ URL::asset('/build/img/products/product7.jpg')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Apple Earpods</a>
                                                        </div>
                                                    </td>
                                                    <td>150</td>
                                                    <td>500</td>
                                                    <td>300</td>
                                                    <td>100</td>
                                                    <td>50</td>
                                                    <td>300</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 ms-auto">
                                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Grand Total</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Order Tax</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Discount</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Shipping</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" value="0" class="p-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-5">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                    <option>Pending</option>
                                                    <option>Received</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button type="button" class="btn btn-cancel add-cancel me-3"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-submit add-sale">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit popup -->
@endif

@if (Route::is(['quotation-list']))
    <!--Add Quotation -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog purchase modal-dialog-centered stock-adjust-modal">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Quotation</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="quotationList">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="input-blocks add-product">
                                            <label>Customer Name</label>
                                            <div class="row">
                                                <div class="col-lg-10 col-sm-10 col-10">
                                                    <select class="select">
                                                        <option>Choose</option>
                                                        <option>Benjamin</option>
                                                        <option>Nydia Fitzgerald</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-sm-2 col-2 p-0">
                                                    <div class="add-icon tab">
                                                        <a class="btn btn-filter" data-bs-toggle="modal"
                                                            data-bs-target="#add-units"><img
                                                                src="{{ URL::asset('/build/img/icons/plus1.svg')}}" alt="img">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Date</label>

                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker" placeholder="Choose">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="input-blocks">
                                            <label>Reference Number</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Product Name</label>
                                            <div class="input-groupicon select-code">
                                                <input type="text"
                                                    placeholder="Please type product code and select">
                                                <div class="addonset">
                                                    <img src="{{ URL::asset('/build/img/icons/qrcode-scan.svg')}}" alt="img">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="modal-body-table">
                                            <div class="table-responsive">
                                                <table class="table  datanew">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Qty</th>
                                                            <th>Purchase Price($)</th>
                                                            <th>Discount($)</th>
                                                            <th>Tax(%)</th>
                                                            <th>Tax Amount($)</th>
                                                            <th>Unit Cost($)</th>
                                                            <th>Total Cost(%)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr style="background: #ffffff;">
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                            <td class="p-5">

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="input-blocks mb-3">
                                                <label>Order Tax</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="input-blocks mb-3">
                                                <label>Discount</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="input-blocks mb-3">
                                                <label>Shipping</label>
                                                <input type="text" value="0">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="input-blocks mb-3">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Choose</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="input-blocks summer-description-box">
                                        <label>Description</label>
                                        <div id="summernote"></div>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Quotation -->

    <!-- edit popup -->
    <div class="modal fade" id="edit-units">
        <div class="modal-dialog edit-sales-modal">
            <div class="modal-content">
                <div class="page-wrapper p-0 m-0">
                    <div class="content p-0">
                        <div class="page-header p-4 mb-0">
                            <div class="add-item new-sale-items d-flex">
                                <div class="page-title">
                                    <h4>Edit Quotation</h4>
                                </div>
                                <button type="button" class="close" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form action="quotationList">
                                    <div class="row">
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Customer Name</label>
                                                <div class="row">
                                                    <div class="col-lg-10 col-sm-10 col-10">
                                                        <select class="select">
                                                            <option>Thomas</option>
                                                            <option>Rose</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                        <div class="add-icon">
                                                            <span class="choose-add"><i data-feather="plus-circle"
                                                                    class="plus"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Date</label>
                                                <div class="input-groupicon calender-input">
                                                    <i data-feather="calendar" class="info-img"></i>
                                                    <input type="text" class="datetimepicker"
                                                        placeholder="19 jan 2023">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Reference Number</label>
                                                <input type="text" placeholder="010203">
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6 col-12">
                                            <div class="input-blocks">
                                                <label>Product Name</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text"
                                                        placeholder="Please type product code and select">
                                                    <div class="addonset">
                                                        <img src="{{ URL::asset('/build/img/icons/scanners.svg')}}" alt="img">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive no-pagination">
                                        <table class="table  datanew">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th>Purchase Price($)</th>
                                                    <th>Discount($)</th>
                                                    <th>Tax(%)</th>
                                                    <th>Tax Amount($)</th>
                                                    <th>Unit Cost($)</th>
                                                    <th>Total Cost(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Nike Jordan</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>2000</td>
                                                    <td>500</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>1500</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-03.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>3000</td>
                                                    <td>400</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>1700</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="productimgname">
                                                            <a href="javascript:void(0);"
                                                                class="product-img stock-img">
                                                                <img src="{{ URL::asset('/build/img/products/stock-img-05.png')}}"
                                                                    alt="product">
                                                            </a>
                                                            <a href="javascript:void(0);">Lobar Handy</a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-quantity">
                                                            <span class="quantity-btn">+<i
                                                                    data-feather="plus-circle"
                                                                    class="plus-circle"></i></span>
                                                            <input type="text" class="quntity-input"
                                                                value="2">
                                                            <span class="quantity-btn"><i
                                                                    data-feather="minus-circle"
                                                                    class="feather-search"></i></span>
                                                        </div>
                                                    </td>
                                                    <td>2500</td>
                                                    <td>500</td>
                                                    <td>
                                                        0.00
                                                    </td>
                                                    <td>0.00</td>
                                                    <td>0.00</td>
                                                    <td>2000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 ms-auto">
                                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                                <ul>
                                                    <li>
                                                        <h4>Order Tax</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Discount</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Shipping</h4>
                                                        <h5>$ 0.00</h5>
                                                    </li>
                                                    <li>
                                                        <h4>Grand Total</h4>
                                                        <h5>$5200.00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-3">
                                                <label>Order Tax</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-3">
                                                <label>Discount</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-3">
                                                <label>Shipping</label>
                                                <div class="input-groupicon select-code">
                                                    <input type="text" placeholder="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <div class="input-blocks mb-3">
                                                <label>Status</label>
                                                <select class="select">
                                                    <option>Sent</option>
                                                    <option>Completed</option>
                                                    <option>Inprogress</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="input-blocks summer-description-box">
                                                <label>Description</label>
                                                <div id="summernote5"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button type="button" class="btn btn-cancel add-cancel me-3"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-submit add-sale">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /edit popup -->
@endif

@if (Route::is(['pos']))
    <!-- Modal -->
    <div class="modal fade modal-default pos-modal" id="payment-mod" aria-labelledby="payment">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>PAYMENT</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="formPayment" method="POST">
                @csrf
                <div class="modal-body p-4">
                        <label>TOTAL PAYMENT</label>
                        <h2 class="text-center p-4" id="h2total">Rp 4500.00</h2>
                        <div class="input-block txtbox">
                            <label id="label-payment">Order Reference</label>
                            <input class="form-control" type="text" value="" placeholder="">
                        </div>
                        <div class="input-block txtbox">
                            <label id="label-change">Order Reference</label>
                            <input class="form-control" type="text" disabled id="change-money">
                        </div>
                        <div class="modal-footer d-sm-flex justify-content-end">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="pay" data-bs-dismiss="modal">Confirm</button>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Payment Completed -->
    <div class="modal fade modal-default" id="payment-completed" aria-labelledby="payment-completed">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <form action="pos">
                        <div class="icon-head">
                            <a href="{{ url('pos-design') }}">
                                <i data-feather="check-circle" class="feather-40"></i>
                            </a>
                        </div>
                        <h4>Payment Completed</h4>
                        <p class="mb-0">Do you want to Print Receipt for the Completed Order</p>
                        <div class="modal-footer d-sm-flex justify-content-between">
                            <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal"
                                data-bs-target="#print-receipt">Print Receipt<i
                                    class="feather-arrow-right-circle icon-me-5"></i></button>
                            <button type="submit" class="btn btn-secondary flex-fill">Next Order<i
                                    class="feather-arrow-right-circle icon-me-5"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Payment Completed -->

    <!-- Print Receipt -->
    <div class="modal fade modal-default" id="print-receipt" aria-labelledby="print-receipt">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="d-flex justify-content-end">
                    <button type="button" class="close p-0" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="icon-head text-center">
                        <a href="{{ url('pos-design') }}">
                            <img src="{{ URL::asset('/build/img/logo.png')}}" width="100" height="30" alt="Receipt Logo">
                        </a>
                    </div>
                    <div class="text-center info text-center">
                        <h6>Dreamguys Technologies Pvt Ltd.,</h6>
                        <p class="mb-0">Phone Number: +1 5656665656</p>
                        <p class="mb-0">Email: <a href="mailto:example@gmail.com">example@gmail.com</a></p>
                    </div>
                    <div class="tax-invoice">
                        <h6 class="text-center">Tax Invoice</h6>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="invoice-user-name"><span>Name: </span><span>John Doe</span></div>
                                <div class="invoice-user-name"><span>Invoice No: </span><span>CS132453</span></div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="invoice-user-name"><span>Customer Id: </span><span>#LL93784</span></div>
                                <div class="invoice-user-name"><span>Date: </span><span>01.07.2022</span></div>
                            </div>
                        </div>
                    </div>
                    <table class="table-borderless w-100 table-fit">
                        <thead>
                            <tr>
                                <th># Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1. Red Nike Laser</td>
                                <td>$50</td>
                                <td>3</td>
                                <td class="text-end">$150</td>
                            </tr>
                            <tr>
                                <td>2. Iphone 14</td>
                                <td>$50</td>
                                <td>2</td>
                                <td class="text-end">$100</td>
                            </tr>
                            <tr>
                                <td>3. Apple Series 8</td>
                                <td>$50</td>
                                <td>3</td>
                                <td class="text-end">$150</td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <table class="table-borderless w-100 table-fit">
                                        <tr>
                                            <td>Sub Total :</td>
                                            <td class="text-end">$700.00</td>
                                        </tr>
                                        <tr>
                                            <td>Discount :</td>
                                            <td class="text-end">-$50.00</td>
                                        </tr>
                                        <tr>
                                            <td>Shipping :</td>
                                            <td class="text-end">0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Tax (5%) :</td>
                                            <td class="text-end">$5.00</td>
                                        </tr>
                                        <tr>
                                            <td>Total Bill :</td>
                                            <td class="text-end">$655.00</td>
                                        </tr>
                                        <tr>
                                            <td>Due :</td>
                                            <td class="text-end">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Total Payable :</td>
                                            <td class="text-end">$655.00</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center invoice-bar">
                        <p>**VAT against this challan is payable through central registration. Thank you for your
                            business!</p>
                        <a href="{{ url('pos-design') }}">
                            <img src="{{ URL::asset('/build/img/barcode/barcode-03.jpg')}}" alt="Barcode">
                        </a>
                        <p>Sale 31</p>
                        <p>Thank You For Shopping With Us. Please Come Again</p>
                        <a href="javascript:void(0);" class="btn btn-primary">Print Receipt</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Print Receipt -->

    <!-- Products -->
    <div class="modal fade modal-default pos-modal" id="products" aria-labelledby="products">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="me-4">Products</h5>
                        <span class="badge bg-info d-inline-block mb-0">Order ID : #666614</span>
                    </div>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="pos">
                        <div class="product-wrap">
                            <div class="product-list d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-fill">
                                    <a href="javascript:void(0);" class="img-bg me-2">
                                        <img src="{{ URL::asset('/build/img/products/pos-product-16.png')}}" alt="Products">
                                    </a>
                                    <div class="info d-flex align-items-center justify-content-between flex-fill">
                                        <div>
                                            <span>PT0005</span>
                                            <h6><a href="javascript:void(0);">Red Nike Laser</a></h6>
                                        </div>
                                        <p>$2000</p>
                                    </div>
                                </div>

                            </div>
                            <div class="product-list d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-fill">
                                    <a href="javascript:void(0);" class="img-bg me-2">
                                        <img src="{{ URL::asset('/build/img/products/pos-product-17.png')}}" alt="Products">
                                    </a>
                                    <div class="info d-flex align-items-center justify-content-between flex-fill">
                                        <div>
                                            <span>PT0235</span>
                                            <h6><a href="javascript:void(0);">Iphone 14</a></h6>
                                        </div>
                                        <p>$3000</p>
                                    </div>
                                </div>
                            </div>
                            <div class="product-list d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-fill">
                                    <a href="javascript:void(0);" class="img-bg me-2">
                                        <img src="{{ URL::asset('/build/img/products/pos-product-16.png')}}" alt="Products">
                                    </a>
                                    <div class="info d-flex align-items-center justify-content-between flex-fill">
                                        <div>
                                            <span>PT0005</span>
                                            <h6><a href="javascript:void(0);">Red Nike Laser</a></h6>
                                        </div>
                                        <p>$2000</p>
                                    </div>
                                </div>
                            </div>
                            <div class="product-list d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-fill">
                                    <a href="javascript:void(0);" class="img-bg me-2">
                                        <img src="{{ URL::asset('/build/img/products/pos-product-17.png')}}" alt="Products">
                                    </a>
                                    <div class="info d-flex align-items-center justify-content-between flex-fill">
                                        <div>
                                            <span>PT0005</span>
                                            <h6><a href="javascript:void(0);">Red Nike Laser</a></h6>
                                        </div>
                                        <p>$2000</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-sm-flex justify-content-end">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Products -->

    <div class="modal fade" id="create" tabindex="-1" aria-labelledby="create" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="pos">
                        <div class="row">
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Customer Name</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Email</label>
                                    <input type="email" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Phone</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Country</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>City</label>
                                    <input type="text">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks">
                                    <label>Address</label>
                                    <input type="text">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-sm-flex justify-content-end">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hold -->
    <div class="modal fade modal-default pos-modal" id="hold-order" aria-labelledby="hold-order">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Hold order</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                        <h2 class="text-center p-4">4500.00</h2>
                        <div class="input-block">
                            <label>Order Reference</label>
                            <input class="form-control" type="text" value="" placeholder="">
                        </div>
                        <p>The current order will be set on hold. You can retreive this order from the pending order
                            button. Providing a reference to it might help you to identify the order more quickly.</p>
                        <div class="modal-footer d-sm-flex justify-content-end">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="hold" data-bs-dismiss="modal">Confirm</button>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Hold -->

    <!-- Edit Product -->
    <div class="modal fade modal-default pos-modal" id="edit-product" aria-labelledby="edit-product">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5>Red Nike Laser</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="pos">
                        <div class="row">
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Product Name <span>*</span></label>
                                    <input type="text" placeholder="45">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Tax Type <span>*</span></label>
                                    <select class="select">
                                        <option>Exclusive</option>
                                        <option>Inclusive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Tax <span>*</span></label>
                                    <input type="text" placeholder="% 15">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Discount Type <span>*</span></label>
                                    <select class="select">
                                        <option>Percentage</option>
                                        <option>Early payment discounts</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Discount <span>*</span></label>
                                    <input type="text" placeholder="15">
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-12 col-12">
                                <div class="input-blocks add-product">
                                    <label>Sale Unit <span>*</span></label>
                                    <select class="select">
                                        <option>Kilogram</option>
                                        <option>Grams</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-sm-flex justify-content-end">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Product -->

    <!-- Recent Transactions -->
    <div class="modal fade pos-modal" id="recents" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5 class="modal-title">Recent Transactions</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="tabs-sets">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="purchase-tab" data-bs-toggle="tab"
                                    data-bs-target="#purchase" type="button" aria-controls="purchase"
                                    aria-selected="true" role="tab">Purchase</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="payment-tab" data-bs-toggle="tab"
                                    data-bs-target="#payment" type="button" aria-controls="payment"
                                    aria-selected="false" role="tab">Payment</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="return-tab" data-bs-toggle="tab"
                                    data-bs-target="#return" type="button" aria-controls="return"
                                    aria-selected="false" role="tab">Return</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="purchase" role="tabpanel"
                                aria-labelledby="purchase-tab">
                                <div class="table-top">
                                    <div class="search-set">
                                        <div class="search-input">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>
                                    <div class="wordset">
                                        <ul>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Pdf"><img src="{{ URL::asset('/build/img/icons/pdf.svg')}}"
                                                        alt="img"></a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Excel"><img src="{{ URL::asset('/build/img/icons/excel.svg')}}"
                                                        alt="img"></a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Print"><i data-feather="printer"
                                                        class="feather-rotate-ccw"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table datanew">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Amount </th>
                                                <th class="no-sort">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0101</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0102</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0103</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0104</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0105</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0106</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0107</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="payment" role="tabpanel">
                                <div class="table-top">
                                    <div class="search-set">
                                        <div class="search-input">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>
                                    <div class="wordset">
                                        <ul>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Pdf"><img src="{{ URL::asset('/build/img/icons/pdf.svg')}}"
                                                        alt="img"></a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Excel"><img src="{{ URL::asset('/build/img/icons/excel.svg')}}"
                                                        alt="img"></a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center justify-content-center"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Print"><i data-feather="printer"
                                                        class="feather-rotate-ccw"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table datanew">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Amount </th>
                                                <th class="no-sort">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0101</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0102</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0103</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0104</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0105</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0106</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0107</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="return" role="tabpanel">
                                <div class="table-top">
                                    <div class="search-set">
                                        <div class="search-input">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>
                                    <div class="wordset">
                                        <ul>
                                            <li>
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"
                                                    class="d-flex align-items-center justify-content-center"><img
                                                        src="{{ URL::asset('/build/img/icons/pdf.svg')}}" alt="img"></a>
                                            </li>
                                            <li>
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"
                                                    class="d-flex align-items-center justify-content-center"><img
                                                        src="{{ URL::asset('/build/img/icons/excel.svg')}}" alt="img"></a>
                                            </li>
                                            <li>
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"
                                                    class="d-flex align-items-center justify-content-center"><i
                                                        data-feather="printer" class="feather-rotate-ccw"></i></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table datanew">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Reference</th>
                                                <th>Customer</th>
                                                <th>Amount </th>
                                                <th class="no-sort">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0101</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0102</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0103</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0104</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0105</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0106</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>19 Jan 2023</td>
                                                <td>INV/SL0107</td>
                                                <td>Walk-in Customer</td>
                                                <td>$1500.00</td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="eye" class="feather-eye"></i></a>
                                                        <a class="me-2 p-2" href="javascript:void(0);"><i
                                                                data-feather="edit" class="feather-edit"></i></a>
                                                        <a class="p-2 confirm-text" href="javascript:void(0);"><i
                                                                data-feather="trash-2"
                                                                class="feather-trash-2"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Recent Transactions -->

    <!-- Recent Transactions -->
    <div class="modal fade pos-modal" id="orders" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header p-4">
                    <h5 class="modal-title">Orders</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="tabs-sets">
                        <ul class="nav nav-tabs" id="myTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="onhold-tab" data-bs-toggle="tab"
                                    data-bs-target="#onhold" type="button" aria-controls="onhold"
                                    aria-selected="true" role="tab">Onhold</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab"
                                    data-bs-target="#unpaid" type="button" aria-controls="unpaid"
                                    aria-selected="false" role="tab">Unpaid</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="paid-tab" data-bs-toggle="tab"
                                    data-bs-target="#paid" type="button" aria-controls="paid"
                                    aria-selected="false" role="tab">Paid</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="onhold" role="tabpanel"
                                aria-labelledby="onhold-tab">
                                <div class="table-top">
                                    <div class="search-set w-100 search-order">
                                        <div class="search-input w-100">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-secondary d-inline-block mb-4">Order ID : #666659</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Botsford</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$900</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">29-08-2023 13:39:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-sm-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-secondary d-inline-block mb-4">Order ID : #666660</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Smith</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$15000</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">30-08-2023 15:59:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4">
                                        <span class="badge bg-secondary d-inline-block mb-4">Order ID : #666661</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">John David</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$2000</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">01-09-2023 13:15:00</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4 mb-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="unpaid" role="tabpanel">
                                <div class="table-top">
                                    <div class="search-set w-100 search-order">
                                        <div class="search-input">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>

                                </div>
                                <div class="order-body">
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-info d-inline-block mb-4">Order ID : #666662</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Anastasia</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$2500</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">10-09-2023 17:15:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-info d-inline-block mb-4">Order ID : #666663</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Lucia</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$1500</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">11-09-2023 14:50:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-info d-inline-block mb-4">Order ID : #666664</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Diego</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$30000</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">12-09-2023 17:22:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4 mb-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="paid" role="tabpanel">
                                <div class="table-top">
                                    <div class="search-set w-100 search-order">
                                        <div class="search-input">
                                            <a class="btn btn-searchset d-flex align-items-center h-100"><img
                                                    src="{{ URL::asset('/build/img/icons/search-white.svg')}}" alt="img"></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-body">
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-primary d-inline-block mb-4">Order ID : #666665</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Hugo</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$5000</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">13-09-2023 19:39:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-primary d-inline-block mb-4">Order ID : #666666</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">Antonio</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$7000</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">15-09-2023 18:39:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                    <div class="default-cover p-4 mb-4">
                                        <span class="badge bg-primary d-inline-block mb-4">Order ID : #666667</span>
                                        <div class="row">
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr class="mb-3">
                                                        <td>Cashier</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">admin</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">MacQuoid</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-sm-12 col-md-6 record mb-3">
                                                <table>
                                                    <tr>
                                                        <td>Total</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">$7050</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Date</td>
                                                        <td class="colon">:</td>
                                                        <td class="text">17-09-2023 19:39:11</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <p class="p-4 mb-4">Customer need to recheck the product once</p>
                                        <div class="btn-row d-flex align-items-center justify-content-between">
                                            <a href="javascript:void(0);"
                                                class="btn btn-info btn-icon flex-fill">Open</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-danger btn-icon flex-fill">Products</a>
                                            <a href="javascript:void(0);"
                                                class="btn btn-success btn-icon flex-fill">Print</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Recent Transactions -->
@endif

@if (Route::is(['coupons']))
    <!-- Add coupons -->
    <div class="modal fade" id="add-units">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add Coupons</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="coupons">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Code</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Type</label>
                                            <select class="select">
                                                <option>Choose Type</option>
                                                <option>Fixed</option>
                                                <option>Percentage</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Discount</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label class="form-label"> Limit</label>
                                            <input type="text" class="form-control">
                                            <span class="unlimited-text">0 for Unlimited</span>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Start Date</label>

                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker form-control"
                                                    placeholder="Select Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>End Date</label>

                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker form-control"
                                                    placeholder="Select Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-blocks">
                                        <div
                                            class="status-toggle modal-status d-flex justify-content-between align-items-center mb-2">
                                            <span class="status-label">All Products</span>
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox" id="user4" class="check">
                                                <label for="user4" class="checktoggle mb-0 me-1"></label>
                                                <span class="customer-toggle">Once Per Customer</span>
                                            </div>
                                        </div>
                                        <select class="select">
                                            <option>Choose</option>
                                            <option>Nike Jordan</option>
                                            <option>Amazon Echo Dot</option>
                                        </select>
                                    </div>

                                    <div class="input-blocks m-0">
                                        <div
                                            class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                            <span class="status-label">Status</span>
                                            <input type="checkbox" id="user3" class="check" checked>
                                            <label for="user3" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Create Coupon</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Coupons -->

    <!-- Edit coupons -->
    <div class="modal fade" id="edit-units">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Edit Coupons</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form action="coupons">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Name</label>
                                            <input type="text" value="Coupons 21">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Code</label>
                                            <input type="text" value="Christmas">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Type</label>
                                            <select class="select">
                                                <option>Fixed</option>
                                                <option>Percentage</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Discount</label>
                                            <input type="text" value="$20">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Limit</label>
                                            <input type="text" value="04">
                                            <span class="unlimited-text">0 for Unlimited</span>
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Start Date</label>
                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker form-control"
                                                    placeholder="Select Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>End Date</label>
                                            <div class="input-groupicon calender-input">
                                                <i data-feather="calendar" class="info-img"></i>
                                                <input type="text" class="datetimepicker form-control"
                                                    placeholder="Select Date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-blocks">
                                        <div
                                            class="status-toggle modal-status d-flex justify-content-between align-items-center mb-2">
                                            <span class="status-label">All Products</span>
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox" id="user5" class="check">
                                                <label for="user5" class="checktoggle mb-0 me-1"></label>
                                                <span class="customer-toggle">Once Per Customer</span>
                                            </div>
                                        </div>
                                        <select class="select">
                                            <option>Nike Jordan</option>
                                            <option>Amazon Echo Dot</option>
                                        </select>
                                    </div>

                                    <div class="input-blocks m-0">
                                        <div
                                            class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                            <span class="status-label">Status</span>
                                            <input type="checkbox" id="user6" class="check" checked>
                                            <label for="user6" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Coupons -->
@endif

@if(Route::is(['attendance-admin']))
		<!-- Add Attendance -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Attendance</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="attendance-admin">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Employee Name</label>
												<select class="select">
													<option>Choose</option>
													<option>Mitchum Daniel</option>
													<option>Janet Hembre</option>
													<option>Russell Belle</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Clock In</label>
												<div class="form-icon">
													<input type="text" class="form-control timepicker" placeholder="Select">
													<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Clock Out</label>
												<div class="form-icon">
													<input type="text" class="form-control timepicker" placeholder="Select">
													<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
									</div>					
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Attendance -->

		<!-- Edit Warehouse -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Attendance</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="attendance-admin">
									<div class="row">
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Employee Name</label>
												<select class="select">
													<option>Mitchum Daniel</option>
													<option>Janet Hembre</option>
													<option>Russell Belle</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Clock In</label>
												<div class="form-icon">
													<input type="text" class="form-control timepicker" placeholder="09:15 AM">
													<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Clock Out</label>
												<div class="form-icon">
													<input type="text" class="form-control timepicker" placeholder="07:30 PM">
													<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
												</div>
											</div>
										</div>
									</div>					
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Warehouse -->

		<!-- Add Attendance -->
		<div class="modal fade" id="delete-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-deletecontent">
							<i data-feather="x-circle" class="feather-xcircircle"></i>
							<h4>Are You Sure?</h4>
							<p>Do you really want to delete this item, This process cannot be undone.</p>
							<div class="modal-footer-btn delete">
								<a href="javascript:void(0);" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</a>
								<a href="{{url('attendance-admin')}}" class="btn btn-submit">Delete</a>
							</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Attendance -->
@endif

@if(Route::is(['attendance-employee']))
		<!-- Add Attendance -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Attendance</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<div class="row">
									<div class="col-lg-12">
										<div class="input-blocks">
											<label>Employee Name</label>
											<select class="select">
												<option>Choose</option>
												<option>Mitchum Daniel</option>
												<option>Janet Hembre</option>
												<option>Russell Belle</option>
											</select>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
											<label>Clock In</label>
											<div class="form-icon">
												<input type="text" class="form-control timepicker" placeholder="Select">
												<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
											<label>Clock Out</label>
											<div class="form-icon">
												<input type="text" class="form-control timepicker" placeholder="Select">
												<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
											</div>
										</div>
									</div>
								</div>					
								<div class="modal-footer-btn">
									<a href="javascript:void(0);" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</a>
									<a href="{{url('warehouse')}}" class="btn btn-submit">Submit</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Attendance -->

		<!-- Edit Warehouse -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Attendance</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<div class="row">
									<div class="col-lg-12">
										<div class="input-blocks">
											<label>Employee Name</label>
											<select class="select">
												<option>Mitchum Daniel</option>
												<option>Janet Hembre</option>
												<option>Russell Belle</option>
											</select>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
											<label>Clock In</label>
											<div class="form-icon">
												<input type="text" class="form-control timepicker" placeholder="09:15 AM">
												<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
											<label>Clock Out</label>
											<div class="form-icon">
												<input type="text" class="form-control timepicker" placeholder="07:30 PM">
												<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
											</div>
										</div>
									</div>
								</div>					
								<div class="modal-footer-btn">
									<a href="javascript:void(0);" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</a>
									<a href="{{url('warehouse')}}" class="btn btn-submit">Save Changes</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Warehouse -->
@endif

@if(Route::is(['ban-ip-address']))
		<!-- Add BanIp -->
		<div class="modal fade" id="add-banip">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add New Ban IP Address</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="ban-ip-address">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IP Address</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Reason For Ban</label>
												<textarea rows="4" placeholder="Type your message" class="form-control"></textarea>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked="">
												<label for="user5" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add BanIp -->

		<!-- Edit BanIp -->
		<div class="modal fade" id="edit-banip">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Ban IP Address</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="ban-ip-address">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IP Address</label>
												<input type="text" class="form-control" value="211.11.0.25">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Reason For Ban</label>
												<textarea rows="4" class="form-control" placeholder="Temporarily block to protect user accounts from internet fraudsters."></textarea>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user4" class="check" checked="">
												<label for="user4" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit BanIp -->
@endif

@if(Route::is(['bank-settings-grid']))
		<!-- Add Bank Account -->
		<div class="modal fade" id="add-account">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Bank Account</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user1" class="check" checked>
									<label for="user1" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="bank-settings-grid">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Bank Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Number <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Branch <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IFSC <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center mb-3">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user2" class="check" checked="">
												<label for="user2" class="checktoggle"></label>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Make as default</span>
												<input type="checkbox" id="user3" class="check" checked="">
												<label for="user3" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Bank Account -->

		<!-- Edit Bank Account -->
		<div class="modal fade" id="edit-account">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Bank Account</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="bank-settings-grid">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Bank Name <span> *</span></label>
												<input type="text" class="form-control" value="HDFC">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Number <span> *</span></label>
												<input type="text" class="form-control" value="**** **** 1832">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Name <span> *</span></label>
												<input type="text" class="form-control" value="Mathew">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Branch <span> *</span></label>
												<input type="text" class="form-control" value="Bringham">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IFSC <span> *</span></label>
												<input type="text" class="form-control" value="124547">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center mb-3">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked="">
												<label for="user5" class="checktoggle"></label>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Make as default</span>
												<input type="checkbox" id="user6" class="check" checked="">
												<label for="user6" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Bank Account -->
@endif

@if(Route::is(['bank-settings-list']))
		<!-- Add Bank Account -->
		<div class="modal fade" id="add-account">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Bank Account</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user1" class="check" checked>
									<label for="user1" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="bank-settings-list">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Bank Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Number <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Branch <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IFSC <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center mb-3">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user2" class="check" checked="">
												<label for="user2" class="checktoggle"></label>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Make as default</span>
												<input type="checkbox" id="user3" class="check" checked="">
												<label for="user3" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Bank Account -->

		<!-- Edit Bank Account -->
		<div class="modal fade" id="edit-account">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Bank Account</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="bank-settings-list">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Bank Name <span> *</span></label>
												<input type="text" class="form-control" value="HDFC">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Number <span> *</span></label>
												<input type="text" class="form-control" value="**** **** 1832">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Account Name <span> *</span></label>
												<input type="text" class="form-control" value="Mathew">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Branch <span> *</span></label>
												<input type="text" class="form-control" value="Bringham">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IFSC <span> *</span></label>
												<input type="text" class="form-control" value="124547">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center mb-3">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked="">
												<label for="user5" class="checktoggle"></label>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Make as default</span>
												<input type="checkbox" id="user6" class="check" checked="">
												<label for="user6" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Bank Account -->
@endif

@if(Route::is(['calendar']))
            	<!-- Add Event Modal -->
				<div id="add_event" class="modal custom-modal fade" role="dialog">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Add Event</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							</div>
							<div class="modal-body">
								<form action="calendar">
									<div class="input-blocks">
										<label>Event Name <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="input-blocks">
										<label>Event Date <span class="text-danger">*</span></label>
										<div class="cal-icon">
											<input class="form-control " type="text">
										</div>
									</div>
									<div class="submit-section">
										<button type="submit" class="btn btn-primary submit-btn">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!-- /Add Event Modal -->
				
                <!-- Add Event Modal -->
                <div class="modal custom-modal fade none-border" id="my_event">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Event</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            </div>
                            <div class="modal-body">
								<form action="calendar">
									<div class="modal-footer justify-content-center">
										<button type="button" class="btn btn-success save-event submit-btn">Create event</button>
										<button type="submit" class="btn btn-danger delete-event submit-btn" data-dismiss="modal">Delete</button>
									</div>
								</form>
							</div>
                        </div>
                    </div>
                </div>
				<!-- /Add Event Modal -->
				
                <!-- Add Category Modal -->
                <div class="modal custom-modal fade" id="add_new_event">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Category</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"><span aria-hidden="true">×</span></button>
                            </div>
                            <div class="modal-body">
                                <form action="calendar">
									<div class="mb-3">
										<label class="form-label">Category Name</label>
										<input class="form-control form-white" placeholder="Enter name" type="text" name="category-name">
									</div>
									<div class="mb-3">
										<label class="form-label">Choose Category Color</label>
										<select class="form-control form-white" data-placeholder="Choose a color..." name="category-color">
											<option value="success">Success</option>
											<option value="danger">Danger</option>
											<option value="info">Info</option>
											<option value="primary">Primary</option>
											<option value="warning">Warning</option>
											<option value="inverse">Inverse</option>
										</select>
									</div>
									<div class="submit-section">
										<button type="submit" class="btn btn-primary save-category submit-btn" data-dismiss="modal">Save</button>
									</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Add Category Modal -->
@endif

@if(Route::is(['countires']))
		<!-- Add Supplier -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Country</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="countries">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Country Name</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Region</label>
												<input type="text" class="form-control">
											</div>
										</div>
										
										<div class="row">
											<!-- Editor -->
											<div class="col-md-12">
												<div class="edit-add card">
													<div class="edit-add">
														<label class="form-label">Description</label>
													</div>
													<div class="card-body-list">
														<div id="summernote">Type your message</div>
													</div>
													<p>Maximum 600 Characters</p>
												</div>
											</div>
											<!-- /Editor -->
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Supplier -->

		<!-- Edit Supplier -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Supplier</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="countries">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Country Name</label>
												<input type="text" class="form-control" placeholder="China">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Region</label>
												<input type="text" class="form-control" placeholder="Beijing">
											</div>
										</div>
										
										<div class="row">
											<!-- Editor -->
											<div class="col-md-12">
												<div class="edit-add card">
													<div class="edit-add">
														<label class="form-label">Description</label>
				
													</div>
													<div class="card-body-list">
														<div id="summernote5">Type your message</div>
													</div>
													<p>Maximum 600 Characters</p>
												</div>
											</div>
											<!-- /Editor -->
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Supplier -->
@endif

@if(Route::is(['currency-settings']))
		<!-- Add Currency -->
		<div class="modal fade" id="add-currency">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Currency</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="currency-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Symbol <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Code <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Rate <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user6" class="check" checked="">
												<label for="user6" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Currency -->

		<!-- Edit Currency -->
		<div class="modal fade" id="edit-currency">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Currency</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="currency-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Name <span> *</span></label>
												<input type="text" class="form-control" value="Euro">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Symbol <span> *</span></label>
												<input type="text" class="form-control" value="EUR">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Code <span> *</span></label>
												<input type="text" class="form-control" value="€">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Currency Rate <span> *</span></label>
												<input type="text" class="form-control" value="Default">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked="">
												<label for="user5" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Currency -->
@endif

@if(Route::is(['custom-fields']))
		<!-- Add Custom Field -->
		<div class="modal fade" id="add-custom-field">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add New Custom Fields</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="custom-fields">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Custom Fields For <span> *</span></label>
												<select class="select">
													<option>Choose</option>
													<option>Expense</option>
													<option>Transaction</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Label <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Default Value <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Input Type <span> *</span></label>
												<select class="select">
													<option>Choose</option>
													<option>Text</option>
													<option>Textarea</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="localization-select pos-payment-method mb-3 d-flex align-items-center mb-0 w-100">
												<div class="custom-control custom-checkbox">
													<label class="checkboxs mb-0 pb-0 line-height-1">
														<input type="checkbox" checked>
														<span class="checkmarks"></span>Required
													</label>
												</div>
												<div class="custom-control custom-checkbox">
													<label class="checkboxs mb-0 pb-0 line-height-1">
														<input type="checkbox" checked>
														<span class="checkmarks"></span>Disable
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user2" class="check" checked="">
												<label for="user2" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Custom Field -->

		<!-- Edit Custom Field -->
		<div class="modal fade" id="edit-custom-field">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add New Custom Fields</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="custom-fields">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Custom Fields For <span> *</span></label>
												<select class="select">
													<option>Expense</option>
													<option>Transaction</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Label <span> *</span></label>
												<input type="text" class="form-control" value="Name">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Default Value <span> *</span></label>
												<input type="text" class="form-control" value="None">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Input Type <span> *</span></label>
												<select class="select">
													<option>Text</option>
													<option>Textarea</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="localization-select pos-payment-method mb-3 d-flex align-items-center mb-0 w-100">
												<div class="custom-control custom-checkbox">
													<label class="checkboxs mb-0 pb-0 line-height-1">
														<input type="checkbox" checked>
														<span class="checkmarks"></span>Required
													</label>
												</div>
												<div class="custom-control custom-checkbox">
													<label class="checkboxs mb-0 pb-0 line-height-1">
														<input type="checkbox" checked>
														<span class="checkmarks"></span>Disable
													</label>
												</div>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user3" class="check" checked="">
												<label for="user3" class="checktoggle"></label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Custom Field -->
@endif

@if(Route::is(['department-grid','department-list']))
		<!-- Add Department -->
		<div class="modal fade" id="add-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Department</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="department-grid">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Department Name</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">HOD</label>
												<select class="select">
													<option>Choose Type</option>
													<option>Mitchum Daniel</option>
													<option>Susan Lopez</option>
												</select>
											</div>
										</div>	
										<div class="col-lg-12">
											<div class="mb-3 summer-description-box">
												<label class="form-label">Description</label>
												<div id="summernote"></div>
											</div>
										</div>			
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked>
												<label for="user5" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Department -->

		<!-- Edit Department -->
		<div class="modal fade" id="edit-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Department</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="department-grid">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Department Name</label>
												<input type="text" class="form-control" value="UI/UX">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">HOD</label>
												<select class="select">
													<option>Mitchum Daniel</option>
													<option>Susan Lopez</option>
												</select>
											</div>
										</div>	
										<div class="col-lg-12">
											<div class="mb-3 summer-description-box">
												<label class="form-label">Description</label>
												<div id="summernote2"></div>
											</div>
										</div>			
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user3" class="check" checked>
												<label for="user3" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Department -->
@endif

@if(Route::is(['designation']))
		<!-- Add Department -->
		<div class="modal fade" id="add-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Designation</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="designation">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Designation Name</label>
												<input type="text" class="form-control">
											</div>
										</div>		
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked>
												<label for="user5" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Department -->

		<!-- Edit Department -->
		<div class="modal fade" id="edit-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Designation </h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="designation">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Designation  Name</label>
												<input type="text" class="form-control" value="Designer">
											</div>
										</div>		
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user3" class="check" checked>
												<label for="user3" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Department -->
@endif

@if(Route::is(['email-settings']))
		<!-- Php Mail -->
		<div class="modal fade" id="php-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>PHP Mailer</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="email-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">From Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Email Password <span> *</span></label>
												<input type="password" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> From Email Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Php Mail -->

		<!-- Smtp Mail -->
		<div class="modal fade" id="smtp-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>SMTP</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user5" class="check" checked>
									<label for="user5" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="email-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">From Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Email Password <span> *</span></label>
												<input type="password" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label"> Email Host <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Port <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Smtp Mail -->

		<!-- Test Mail -->
		<div class="modal fade" id="test-mail">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Test Mail</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user6" class="check" checked>
									<label for="user6" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="email-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Enter Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Test Mail -->
@endif

@if(Route::is(['file-manager']))
	<!-- Files Toogle Slide -->
	<div class="toggle-sidebar">
		<div class="d-flex align-items-center justify-content-between head">
			<h4>File Preview</h4>
			<div class="d-flex align-items-center">
				<a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i class="fa fa-star"></i></a>
				<a href="javascript:void(0);" class="me-2 d-flex align-items-center"><i data-feather="trash-2" class="feather-16 text-center text-danger"></i></a>
				<a href="javascript:void(0);" class="sidebar-closes d-flex align-items-center" aria-hidden="true"><i data-feather="x-circle" class="feather-26 color-primary"></i></a>
			</div>
		</div>
		<div class="text-center">
			<a href="javascript:void(0);"><img src="{{ URL::asset('/build/img/file-manager/folder-lg.png')}}" alt="Folder"></a>
			<h5>Website Backup for the Design team</h5>
			<p>File Size : 616 MB</p>
		</div>

		<div class="nav nav-tabs d-flex align-items-center justify-content-between py-4 mb-4" id="nav-tab" role="tablist">
			<a class="nav-link flex-fill active btn btn-light me-2 text-center" id="nav-home-tab" data-bs-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><i data-feather="list" class="feather-16 me-2 text-center"></i>Details</a>
			<a class="nav-link flex-fill btn btn-light" id="nav-profile-tab" data-bs-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><i data-feather="clock" class="feather-16 me-2"></i>Activity</a>
		</div>
		<div class="tab-content" id="nav-tabContent">
			<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
				<h5 class="mb-4 d-flex align-items-center"><i data-feather="edit" class="feather-20 me-2"></i>Properties</h5>
				<ul class="seprator-lg">
					<li class="mb-4">
						<h6>File Name</h6>
						<p>Website Backup for the Designteam</p>
					</li>
					<li class="mb-4">
						<h6>File Type</h6>
						<p>Folder</p>
					</li>
					<li class="mb-4">
						<h6>Size</h6>
						<p>616 MB</p>
					</li>
					<li class="mb-4">
						<h6>Created</h6>
						<p>22 July 2023, 08:30 PM</p>
					</li>
					<li class="mb-4">
						<h6>Location</h6>
						<p class="location d-inline-flex align-items-center"><i data-feather="hard-drive" class="feather-16 me-1"></i>Drive</p>
					</li>
					<li class="mb-4">
						<h6>File Name</h6>
						<p>23 July 2023, 08:30 PM</p>
					</li>
					<li class="mb-4">
						<h6>Opened On</h6>
						<p>28 July 2023, 06:40 PM</p>
					</li>
					<li>
						<div class="row">
							<!-- Editor -->
							<div class="col-lg-12">
								<div class="input-blocks summer-description-box transfer">
									<label>Description</label>
									<div id="summernote3">
									</div>
									<p>Maximum 60 Characters</p>
								</div>
							</div>
							<!-- /Editor -->
						</div>
					</li>
				</ul>
				<h5 class="mb-4 d-flex align-items-center"><i data-feather="user" class="feather-20 me-2"></i>Who has access</h5>
				<div class="d-flex align-items-center justify-content-between avatar-wrap">
					<div class="avatar-access d-flex align-items-center mb-4">
						<span>
							<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Member 1" data-bs-original-title="Member 1"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
						</span>
						<span>
							<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Member 2" data-bs-original-title="Member 2"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
						</span>
						<span>
							<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Member 3" data-bs-original-title="Member 3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
						</span>
						<span>
						   <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Member 4" data-bs-original-title="Member 4"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
						</span>
						<span>
						   <a href="javascript:void(0);" class="avatar-md add d-flex align-items-center justify-content-center"><i data-feather="plus" class="feather-16 me-1"></i></a>
						</span>
					</div>
				</div>
				<p>Owned by Andrew. Shared with James, Fin, Davis</p>
			</div>
			<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
				<h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar" class="feather-20 me-2"></i>This Week</h5>
				<ul class="mb-4">
					<li class="mb-4">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
						</div>
						<p class="d-flex align-items-center location border-0"><img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for the Design team</p>
					</li>
					<li class="mb-4">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<p>Drake shared an item<br>3:39 PM Jul 19</p>
						</div>
						<p class="d-flex align-items-center location border-0"><img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for the Design team</p>
					</li>
					<li class="mb-2">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<div><p class="mb-0 text-secondary">Melvin</p><p class="mb-0">Commentor</p></div>
						</div>
					</li>
					<li class="mb-2">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<div><p class="mb-0 text-secondary">Drake</p><p class="mb-0">Editor</p></div>
						</div>
					</li>
				</ul>
				<h5 class="mb-4 d-flex align-items-center"><i data-feather="calendar" class="feather-20 me-2"></i>Last Month</h5>
				<ul class="mb-4">
					<li class="mb-4">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-1.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<p>Andrew commented on 1 items <br>3:39 PM Jul 19</p>
						</div>
						<p class="d-flex align-items-center location border-0"><img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for the Design team</p>
					</li>
					<li class="mb-4">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-2.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<p>Drake shared an item<br>3:39 PM Jul 19</p>
						</div>
						<p class="d-flex align-items-center location border-0"><img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">Website Backup for the Design team</p>
					</li>
					<li class="mb-2">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-3.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<div><p class="mb-0 text-secondary">Melvin</p><p class="mb-0">Commentor</p></div>
						</div>
					</li>
					<li class="mb-2">
						<div class="d-flex align-items-center mb-2">
							<a href="javascript:void(0);" class="me-3"><img src="{{ URL::asset('/build/img/avatar/avatar-4.jpg')}}" alt="Avatar" class="avatar-md"></a>
							<div><p class="mb-0 text-secondary">Drake</p><p class="mb-0">Editor</p></div>
						</div>
					</li>
				</ul>
				<a href="javascript:void(0);" class="text-primary show-all"><i data-feather="plus-circle" class="feather-20 me-2"></i>Show All</a>
			</div>
		</div>

	</div>
	<!-- Files Toogle Slide -->

	<!-- Upload File -->
	<div class="modal fade modal-default pos-modal upload-modal" id="upload-file" aria-labelledby="upload-file">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-4">
					<h5>Upload File</h5>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body p-4">
					<div class="drag-drop text-center mb-4">
						<div class="upload">
							<a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
							<p>Drag and drop a <a href="#">file to upload</a></p>
						</div>
						<input type="file" multiple="">
					</div>

					<div class="d-flex align-items-center justify-content-between">
						<p>3 of 1 files Uploaded</p>
						<span>70%</span>
					</div>
					<div class="progress mt-2 mb-4">
						  <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
					</div>

					<ul>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">latest-version.zip<i data-feather="check-circle" class="ms-2 feather-16"></i></a></h6>
									<span>616 MB</span>
								</div>
							</div>
							<a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2" class="feather-16"></i></a>
						</li>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">Update work history.xls</a></h6>
									<span>616 MB</span>
									<div class="progress mt-2">
										  <div class="progress-bar bg-danger" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-danger me-2 d-flex align-items-center"><i data-feather="trash-2" class="feather-16"></i></a>
								<a href="javascript:void(0);" class="text-default d-flex align-items-center"><i data-feather="pause-circle" class="feather-16"></i></a>
							</div>
						</li>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">Updated Project.zip</a></h6>
									<span>616 MB</span>
									<div class="progress mt-2">
										  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-danger me-2 d-flex align-items-center"><i data-feather="trash-2" class="feather-16"></i></a>
								<a href="javascript:void(0);" class="text-default d-flex align-items-center"><i data-feather="play-circle" class="feather-16"></i></a>
							</div>
						</li>
					</ul>

				</div>
			</div>
		</div>
	</div>
	<!-- /Upload File -->

	<!-- Upload Folder -->
	<div class="modal fade modal-default pos-modal upload-modal" id="upload-folder" aria-labelledby="upload-folder">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-4">
					<h5>Upload File</h5>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body p-4">
					<div class="drag-drop text-center mb-4">
						<div class="upload">
							<a href="#"><img src="{{ URL::asset('/build/img/icons/drag-drop.svg')}}" alt=""></a>
							<p>Drag and drop a <a href="#">file to upload</a></p>
						</div>
						<input type="file" multiple="">
					</div>

					<div class="d-flex align-items-center justify-content-between">
						<p>3 of 3 files Uploaded</p>
						<span>100%</span>
					</div>
					<div class="progress mt-2 mb-4">
						  <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
					</div>

					<ul>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/folder.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">latest-version<i data-feather="check-circle" class="ms-2 feather-16"></i></a></h6>
									<span>616 MB</span>
								</div>
							</div>
							<a href="javascript:void(0);" class="text-danger text-right"><i data-feather="trash-2" class="feather-16"></i></a>
						</li>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/xls.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">Update work history.xls<i data-feather="trash-2" class="feather-16"></i></a></h6>
									<span>16 MB</span>
								</div>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2" class="feather-16"></i></a>
								<a href="javascript:void(0);" class="text-default"><i data-feather="pause-circle" class="feather-16"></i></a>
							</div>
						</li>
						<li class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center w-85">
								<img src="{{ URL::asset('/build/img/icons/zip.svg')}}" alt="Folder" class="me-2">
								<div class="flex-fill">
									<h6><a href="javascript:void(0);">updated project.zip<i data-feather="trash-2" class="feather-16"></i></a></h6>
									<span>14 MB</span>
								</div>
							</div>
							<div class="d-flex align-items-center">
								<a href="javascript:void(0);" class="text-danger me-2"><i data-feather="trash-2" class="feather-16"></i></a>
								<a href="javascript:void(0);" class="text-default"><i data-feather="play-circle" class="feather-16"></i></a>
							</div>
						</li>
					</ul>

				</div>
				<div class="modal-footer d-sm-flex justify-content-end">
					 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
					<button type="button" class="btn btn-primary">Upload</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Upload Folder -->

	<!-- Upload Folder -->
	<div class="modal fade modal-default pos-modal upload-message" id="upload-message" aria-labelledby="upload-message">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header p-4">
					<h5>Upload File</h5>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body p-4">

					<div class="d-flex align-items-center justify-content-between">
						<p>3 of 3 files Uploaded</p>
						<span>100%</span>
					</div>
					<div class="progress mt-2 mb-4">
						  <div class="progress-bar progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
					</div>
				</div>
				<div class="modal-footer d-sm-flex justify-content-end">
					 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Clear</button>
					<button type="button" class="btn btn-primary">Upload</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Upload Folder -->

	<!-- Create Folder -->
	<div class="modal fade modal-default pos-modal" id="create-folder" aria-labelledby="create-folder">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-4">
					<h5>Create Folder</h5>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body p-4">
					<div class="row">
						<div class="col-12">
							<label class="form-label">Folder Name</label>
							<input type="text" class="form-control">
						</div>
					</div>
					<div class="modal-footer d-sm-flex justify-content-end">
						 <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary">Create Folder</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Create Folder -->
@endif

@if(Route::is(['holidays']))
		<!-- Add Department -->
		<div class="modal fade" id="add-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Holiday</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="holidays">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label>Add Holiday</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
											<label>Start Date</label>
											<div class="input-groupicon calender-input">
											<i data-feather="calendar" class="info-img"></i>
											<input type="text" class="datetimepicker" placeholder="Select">
											</div>
										</div>		
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
										<label>End Date</label>
										<div class="input-groupicon calender-input">
										<i data-feather="calendar" class="info-img"></i>
										<input type="text" class="datetimepicker" placeholder="Select">
										</div>
									</div>		
									</div>
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>No of Days</label>
												<input type="text" class="form-control" placeholder="01">
											</div>
										</div>	
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user5" class="check" checked>
												<label for="user5" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Department -->

		<!-- Edit Department -->
		<div class="modal fade" id="edit-department">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Holiday</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="holidays">
									<div class="row">
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Add Holiday</label>
												<input type="text" class="form-control" value="Newyear">
											</div>
										</div>		
										<div class="col-lg-6">
											<div class="input-blocks">
											<label>Start Date</label>
											<div class="input-groupicon calender-input">
											<i data-feather="calendar" class="info-img"></i>
											<input type="text" class="datetimepicker" placeholder="01 Jan 2023">
											</div>
										</div>		
									</div>
									<div class="col-lg-6">
										<div class="input-blocks">
										<label>End Date</label>
										<div class="input-groupicon calender-input">
											<i data-feather="calendar" class="info-img"></i>
											<input type="text" class="datetimepicker" placeholder="01 Jan 2023">
										</div>
									</div>		
									</div>
										<div class="mb-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user3" class="check" checked>
												<label for="user3" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Department -->
@endif

@if(Route::is(['leave-types']))
		<!-- Add coupons -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add New Leave Type</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="leave-types">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Name</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Leave Quota</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user3" class="check" checked>
												<label for="user3" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Coupons -->

		<!-- Edit Warehouse -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit  Leave Type</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="leave-types">
									<div class="row">
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Name</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Leave Quota</label>
												<input type="text" value="05">
											</div>
										</div>
										<div class="input-blocks m-0">
											<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
												<span class="status-label">Status</span>
												<input type="checkbox" id="user4" class="check" checked>
												<label for="user4" class="checktoggle">	</label>
											</div>
										</div>
									</div>
									
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Warehouse -->
@endif

@if(Route::is(['leaves-employee']))
		<!-- Add Leave -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Apply Leave</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="leaves-employee">
									<div class="row">
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Start Date</label>
												
												<div class="input-groupicon calender-input">
													<i data-feather="calendar" class="info-img"></i>
													<input type="text" class="datetimepicker" placeholder="Select From - To Date" >
												</div>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Select Leave Type </label>
												<select class="select">
													<option>Choose</option>
													<option>Sick Leave</option>
													<option>Paternity</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="apply-leave">
												<div class="leave-apply">
													<div class="leave-date">
														<span>Day 1</span>
														<p>16 Aug 2023</p>
													</div>
													<div class="leave-time">
														<div class="input-blocks mb-0">
															<select class="select">
																<option>Full Day</option>
																<option>Half Day</option>
															</select>
														</div>
													</div>
												</div>
												<div class="leave-apply">
													<div class="leave-date">
														<span>Day 1</span>
														<p>16 Aug 2023</p>
													</div>
													<div class="leave-time">
														<div class="input-blocks mb-0">
															<select class="select">
																<option>Full Day</option>
																<option>Half Day</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-lg-12">
											<div class="mb-3 summer-description-box mb-0">
												<label class="form-label">Reason</label>
												<div id="summernote"></div>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Leave -->

		<!-- Edit Leave -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Leave</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="leaves-employee">
									<div class="row">
										<div class="col-lg-12">
											<div class="input-blocks">
												<label class="form-label">Start Date</label>
												
												<div class="input-groupicon calender-input">
													<i data-feather="calendar" class="info-img"></i>
													<input type="text" class="datetimepicker" placeholder="Select From - To Date" >
												</div>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Select Leave Type </label>
												<select class="select">
													<option>Sick Leave</option>
													<option>Paternity</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="apply-leave">
												<div class="leave-apply">
													<div class="leave-date">
														<span>Day 1</span>
														<p>16 Aug 2023</p>
													</div>
													<div class="leave-time">
														<div class="input-blocks mb-0">
															<select class="select">
																<option>Full Day</option>
																<option>Half Day</option>
															</select>
														</div>
													</div>
												</div>
												<div class="leave-apply">
													<div class="leave-date">
														<span>Day 1</span>
														<p>16 Aug 2023</p>
													</div>
													<div class="leave-time">
														<div class="input-blocks mb-0">
															<select class="select">
																<option>Full Day</option>
																<option>Half Day</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-lg-12">
											<div class="mb-3 summer-description-box mb-0">
												<label class="form-label">Reason</label>
												<div id="summernote2"></div>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Leave -->

		<!-- Rejected Reason -->
		<div class="modal fade" id="rejected-reason">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Reason For Rejection</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<p>The Jordan brand is owned by Nike (owned by the Knight family), as, at the time, the company was building its strategy to work with athletes to launch shows that could inspire consumers.Although Jordan preferred Converse and Adidas, they simply could not match the offer Nike made. Jordan also signed with Nike because he loved the way they wanted to market him with the banned colored shoes. Nike promised to cover the fine Jordan would receive from the NBA.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Rejected Reason -->
@endif

@if(Route::is(['manage-stocks']))
		<!-- Add Stock -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Stock</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="manage-stocks">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Warehouse</label>
												<select class="select">
													<option>Choose</option>
													<option>Lobar Handy</option>
													<option>Quaint Warehouse</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Shop</label>
												<select class="select">
													<option>Choose</option>
													<option>Selosy</option>
													<option>Logerro</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Responsible Person</label>
												<select class="select">
													<option>Choose</option>
													<option>Steven</option>
													<option>Gravely</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="input-blocks search-form mb-0">
												<label>Product</label>
												<input type="text" class="form-control" placeholder="Select Product">
												<i data-feather="search" class="feather-search"></i>
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Create</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Stock -->

		<!-- Edit Stock -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Stock</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="manage-stocks">
									<div class="input-blocks search-form">
										<label>Product</label>
										<input type="text" class="form-control" value="Nike Jordan">
										<i data-feather="search" class="feather-search"></i>
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Warehouse</label>
												<select class="select">
													<option>Lobar Handy</option>
													<option>Quaint Warehouse</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Shop</label>
												<select class="select">
													<option>Selosy</option>
													<option>Logerro</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Responsible Person</label>
												<select class="select">
													<option>Steven</option>
													<option>Gravely</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="input-blocks search-form mb-3">
												<label>Product</label>
												<input type="text" class="form-control" placeholder="Select Product" value="Nike Jordan">
												<i data-feather="search" class="feather-search"></i>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="modal-body-table">
												<div class="table-responsive">
													<table class="table  datanew">
														<thead>
															<tr>
																<th>Product</th>
																<th>SKU</th>
																<th>Category</th>
																<th>Qty</th>
																<th class="no-sort">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>
																	<div class="productimgname">
																		<a href="javascript:void(0);" class="product-img stock-img">
																			<img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}" alt="product">
																		</a>
																		<a href="javascript:void(0);">Nike Jordan</a>
																	</div>												
																</td>
																<td>PT002</td>
																<td>Nike</td>
																<td>
																	<div class="product-quantity">
																		<span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
																		<input type="text" class="quntity-input" value="2">
																		<span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
																	</div>
																</td>
																<td class="action-table-data">
																	<div class="edit-delete-action">
																		<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
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
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Stock -->
@endif

@if(Route::is(['payment-gateway-settings']))
		<!-- Php Mail -->
		<div class="modal fade" id="payment-connect">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Paypal</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user16" class="check" checked>
									<label for="user16" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="payment-gateway-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Email Address <span> *</span></label>
												<input type="email" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Php Mail -->
@endif

@if(Route::is(['printer-settings']))
		<!-- Add Printer -->
		<div class="modal fade" id="add-printer">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Printer</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="printer-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Printer Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Connection Type <span> *</span></label>
												<select class="select">
													<option>Choose</option>
													<option>Network</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IP Address <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Port <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Printer -->

		<!-- Edit Printer -->
		<div class="modal fade" id="edit-printer">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Printer</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="printer-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Printer Name <span> *</span></label>
												<input type="text" class="form-control" value="HP Printer">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Connection Type <span> *</span></label>
												<select class="select">
													<option>Network</option>
												</select>
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">IP Address <span> *</span></label>
												<input type="text" class="form-control" value="151.00.1.22">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Port <span> *</span></label>
												<input type="text" class="form-control" value="900">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Printer -->
@endif

@if(Route::is(['shift']))
		<!-- Add Shift -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add New Shift</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="shift">
									<ul class="nav nav-pills modal-table-tab" id="pills-tab" role="tablist">
										<li class="nav-item" role="presentation">
										<button class="nav-link active" id="pills-add-shift-info-tab" data-bs-toggle="pill" data-bs-target="#pills-add-shift-info" type="button" role="tab" aria-controls="pills-add-shift-info" aria-selected="true">Shift Info</button>
										</li>
										<li class="nav-item" role="presentation">
										<button class="nav-link" id="pills-add-break-tab" data-bs-toggle="pill" data-bs-target="#pills-add-break" type="button" role="tab" aria-controls="pills-add-break" aria-selected="false">Break Timings</button>
										</li>
									</ul>
									<div class="tab-content" id="pills-tabContent">
										<div class="tab-pane fade show active" id="pills-add-shift-info" role="tabpanel" aria-labelledby="pills-add-shift-info-tab">
											<div class="row">
												<div class="col-lg-12">
													<div class="input-blocks">
														<label>Shift Name</label>
														<input type="text" class="form-control">
													</div>
												</div>
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>From</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>To</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
												<div class="col-lg-12">
													<div class="input-blocks">
														<label>Weekoff</label>
														<select class="select">
															<option>Choose</option>
															<option>Sunday, Monday</option>
															<option>Saturday, Sunday</option>
															<option>Tuesday, Saturday</option>
														</select>
													</div>
												</div>
												<div class="col-lg-12">
													<div class="modal-table-item">
														<h4>Weekdays Defeniton</h4>
														<div class="table-responsive no-pagination">
															<table class="table  datanew">
																<thead>
																	<tr>
																		<th>Days</th>
																		<th class="text-center">Weeks</th>
																	</tr>
																</thead>
																<tbody>
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day1" class="check">
																				<label for="day1" class="checktoggle"></label>
																				<span class="status-label ms-2">Monday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																		</td>
																	</tr>	
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day2" class="check">
																				<label for="day2" class="checktoggle"></label>
																				<span class="status-label ms-2">Tuesday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>																										
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day3" class="check">
																				<label for="day3" class="checktoggle"></label>
																				<span class="status-label ms-2">Wednesday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day4" class="check">
																				<label for="day4" class="checktoggle"></label>
																				<span class="status-label ms-2">Thursday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day5" class="check">
																				<label for="day5" class="checktoggle"></label>
																				<span class="status-label ms-2">Friday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day6" class="check">
																				<label for="day6" class="checktoggle"></label>
																				<span class="status-label ms-2">Saturday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<div class="status-toggle modal-status d-flex align-items-center">													
																				<input type="checkbox" id="day7" class="check">
																				<label for="day7" class="checktoggle"></label>
																				<span class="status-label ms-2">Sunday</span>
																			</div>							
																		</td>
																		<td>
																			<div class="text-end">
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					All
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					1st
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					2nd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					3rd
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					4th
																				</label>
																				<label class="checkboxs modal-table-check">
																					<input type="checkbox">
																					<span class="checkmarks"></span>
																					5th
																				</label>
																			</div>
																			
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<div class="input-blocks custom-form-check">
															<label class="checkboxs modal-table-check">
																<input type="checkbox">
																<span class="checkmarks"></span>
																Recurring Shift
															</label>
														</div>
														
														<div class="input-blocks m-0">
															<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																<span class="status-label">Status</span>
																<input type="checkbox" id="user6" class="check" checked>
																<label for="user6" class="checktoggle mb-0"></label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="tab-pane fade" id="pills-add-break" role="tabpanel" aria-labelledby="pills-add-break-tab">
											<div class="break-title">
												<h4>Morning Break</h4>
											</div>
											<div class="row">
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>From</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>To</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
											</div>
											<div class="break-title">
												<h4>Lunch</h4>
											</div>
											<div class="row">
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>From</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>To</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
											</div>
											<div class="break-title">
												<h4>Evening Break</h4>
											</div>
											<div class="row">
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>From</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
												<div class="col-lg-6">
													<div class="input-blocks">
														<label>To</label>
														<div class="form-icon">
															<input type="text" class="form-control timepicker" placeholder="Select Time">
															<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
														</div>
													</div>
												</div>
											</div>
											<div class="input-blocks summer-description-box">
												<label>Description</label>
												<div id="summernote"></div>
											</div>
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Shift -->

			<!-- Edit Shift -->
			<div class="modal fade" id="edit-units">
				<div class="modal-dialog modal-dialog-centered custom-modal-two">
					<div class="modal-content">
						<div class="page-wrapper-new p-0">
							<div class="content">
								<div class="modal-header border-0 custom-modal-header">
									<div class="page-title">
										<h4>Edit Shift</h4>
									</div>
									<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body custom-modal-body">
									<form action="shift">
										<ul class="nav nav-pills modal-table-tab" id="pills-tab2" role="tablist">
											<li class="nav-item" role="presentation">
												<button class="nav-link active" id="pills-edit-shift-info-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-shift-info" type="button" role="tab" aria-controls="pills-edit-shift-info" aria-selected="true">Shift Info</button>
											</li>
											<li class="nav-item" role="presentation">
												<button class="nav-link" id="pills-edit-break-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-break" type="button" role="tab" aria-controls="pills-edit-break" aria-selected="false">Break Timings</button>
											</li>
										</ul>
										<div class="tab-content" id="pills-tabContent2">
											<div class="tab-pane fade show active" id="pills-edit-shift-info" role="tabpanel" aria-labelledby="pills-edit-shift-info-tab">
												<div class="row">
													<div class="col-lg-12">
														<div class="input-blocks">
															<label>Shift Name</label>
															<input type="text" class="form-control">
														</div>
													</div>
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>From</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="Select Time">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>To</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="Select Time">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
													<div class="col-lg-12">
														<div class="input-blocks">
															<label>Weekoff</label>
															<select class="select">
																<option>Sunday, Monday</option>
																<option>Saturday, Sunday</option>
																<option>Tuesday, Saturday</option>
															</select>
														</div>
													</div>
													<div class="col-lg-12">
														<div class="modal-table-item">
															<h4>Weekdays Defeniton</h4>
															<div class="table-responsive no-pagination">
																<table class="table  datanew">
																	<thead>
																		<tr>
																			<th>Days</th>
																			<th class="text-center">Weeks</th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days1" class="check" checked>
																					<label for="days1" class="checktoggle"></label>
																					<span class="status-label ms-2">Monday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																			</td>
																		</tr>	
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days2" class="check" checked>
																					<label for="days2" class="checktoggle"></label>
																					<span class="status-label ms-2">Tuesday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>																										
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days3" class="check" checked>
																					<label for="days3" class="checktoggle"></label>
																					<span class="status-label ms-2">Wednesday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days4" class="check" checked>
																					<label for="days4" class="checktoggle"></label>
																					<span class="status-label ms-2">Thursday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox" checked>
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days5" class="check">
																					<label for="days5" class="checktoggle"></label>
																					<span class="status-label ms-2">Friday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days6" class="check">
																					<label for="days6" class="checktoggle"></label>
																					<span class="status-label ms-2">Saturday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>
																		<tr>
																			<td>
																				<div class="status-toggle modal-status d-flex align-items-center">													
																					<input type="checkbox" id="days7" class="check">
																					<label for="days7" class="checktoggle"></label>
																					<span class="status-label ms-2">Sunday</span>
																				</div>							
																			</td>
																			<td>
																				<div class="text-end">
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						All
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						1st
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						2nd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						3rd
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						4th
																					</label>
																					<label class="checkboxs modal-table-check">
																						<input type="checkbox">
																						<span class="checkmarks"></span>
																						5th
																					</label>
																				</div>
																				
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
															<div class="input-blocks custom-form-check">
																<label class="checkboxs modal-table-check">
																	<input type="checkbox" checked>
																	<span class="checkmarks"></span>
																	Recurring Shift
																</label>
															</div>
															
															<div class="input-blocks m-0">
																<div class="status-toggle modal-status d-flex justify-content-between align-items-center">
																	<span class="status-label">Status</span>
																	<input type="checkbox" id="users6" class="check" checked>
																	<label for="users6" class="checktoggle mb-0"></label>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="tab-pane fade" id="pills-edit-break" role="tabpanel" aria-labelledby="pills-edit-break-tab">
												<div class="break-title">
													<h4>Morning Break</h4>
												</div>
												<div class="row">
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>From</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="11:00 AM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>To</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="11:15 AM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
												</div>
												<div class="break-title">
													<h4>Lunch</h4>
												</div>
												<div class="row">
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>From</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="01:00 PM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>To</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="02:00 PM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
												</div>
												<div class="break-title">
													<h4>Evening Break</h4>
												</div>
												<div class="row">
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>From</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="05:00 PM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
													<div class="col-lg-6">
														<div class="input-blocks">
															<label>To</label>
															<div class="form-icon">
																<input type="text" class="form-control timepicker" placeholder="05:30 PM">
																<span class="cus-icon"><i data-feather="clock" class="feather-clock"></i></span>
															</div>
														</div>
													</div>
												</div>
												<div class="input-blocks summer-description-box">
													<label>Description</label>
													<div id="summernote2"></div>
												</div>
											</div>
										</div>
										
										<div class="modal-footer-btn">
											<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
											<button type="submit" class="btn btn-submit">Submit</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Edit Shift -->
@endif

@if(Route::is(['sms-gateway']))
		<!-- nexmo Config -->
		<div class="modal fade" id="nexmo-config">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Nexmo</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="sms-gateway">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Sender ID <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /nexmo Config -->

		<!-- Two Factor Config-->
		<div class="modal fade" id="factor-config">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>2Factor</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="users4" class="check" checked>
									<label for="users4" class="checktoggle"></label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="sms-gateway">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Sender ID <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Two Factor Config -->

		<!-- Twilio Config -->
		<div class="modal fade" id="twilio-config">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Twilio</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user14" class="check" checked>
									<label for="user14" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="sms-gateway">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">API Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Sender ID <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Twilio Config -->
@endif

@if(Route::is(['social-authentication']))
		<!-- Connect Facebook -->
		<div class="modal fade" id="fb-connect">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Facebook Login Settings</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="social-authentication">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">App ID <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">App Secret <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Login Redirect URL <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Connect Facebook -->

		<!-- Connect Twitter -->
		<div class="modal fade" id="twitter-connect">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Twitter Login Settings</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="social-authentication">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Consumer Key (API Key) <span> *</span></label>
												<input type="text" class="form-control">
												<p class="input-notify-info">If you are not sure what is your APP ID, Please head over to <span>Getting Started.</span></p>
											</div>
											<div class="mb-3">
												<label class="form-label">Consumer Secret (Secret Key) <span> *</span></label>
												<input type="text" class="form-control">
											</div>
											<div class="mb-0">
												<label class="form-label">Login Redirect URL <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Connect Twitter -->

		<!-- Connect Google -->
		<div class="modal fade" id="google-connect">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Google Login Settings</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="social-authentication">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Client ID <span> *</span></label>
												<input type="text" class="form-control">
												<p class="input-notify-info">If you are not sure what is your APP ID, Please head over to <span>Getting Started.</span></p>
											</div>
											<div class="mb-3">
												<label class="form-label">Client Secret Key <span> *</span></label>
												<input type="text" class="form-control">											
											</div>
											<div class="mb-0">
												<label class="form-label">Login Redirect URL <span> *</span></label>
												<input type="text" class="form-control">											
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Connect Google -->

		<!-- Connect Linkedin -->
		<div class="modal fade" id="linkedin-connect">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>LinkedIn Login Settings</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="social-authentication">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Client ID <span> *</span></label>
												<input type="text" class="form-control">
												<p class="input-notify-info">If you are not sure what is your APP ID, Please head over to <span>Getting Started.</span></p>
											</div>
											<div class="mb-3">
												<label class="form-label">Client Secret Key <span> *</span></label>
												<input type="text" class="form-control">											
											</div>
											<div class="mb-0">
												<label class="form-label">Login Redirect URL <span> *</span></label>
												<input type="text" class="form-control">											
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Connect Linkedin -->
@endif

@if(Route::is(['states']))
			<!-- Add State -->
			<div class="modal fade" id="add-units">
				<div class="modal-dialog modal-dialog-centered custom-modal-two">
					<div class="modal-content">
						<div class="page-wrapper-new p-0">
							<div class="content">
								<div class="modal-header border-0 custom-modal-header">
									<div class="page-title">
										<h4>Add State</h4>
									</div>
									<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body custom-modal-body">
									<form action="states">
										<div class="row">
											<div class="col-lg-6">
												<div class="input-blocks">
													<label>State Name</label>
													<input type="text" class="form-control">
												</div>
											</div>
											<div class="col-lg-6">
												<div class="input-blocks">
													<label>Country</label>
													<input type="text" class="form-control">
												</div>
											</div>
											
											<div class="row">
												<!-- Editor -->
												<div class="col-md-12">
													<div class="edit-add card">
														<div class="edit-add">
															<label>Description</label>
					
														</div>
														<div class="card-body-list">
															<div id="summernote">Type your message</div>
														</div>
														<p>Maximum 600 Characters</p>
													</div>
												</div>
												<!-- /Editor -->
											</div>
										</div>
										
										<div class="modal-footer-btn">
											<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
											<button type="submit" class="btn btn-submit">Submit</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Add State -->

		<!-- Edit State -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit State</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="states">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>State Name</label>
												<input type="text" placeholder="Beijing">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Country</label>
												<input type="text" placeholder="China">
											</div>
										</div>
										
										<div class="row">
											<!-- Editor -->
											<div class="col-md-12">
												<div class="edit-add card">
													<div class="edit-add">
														<label>Description</label>
				
													</div>
													<div class="card-body-list">
														<div id="summernote5">Type your message</div>
													</div>
													<p>Maximum 600 Characters</p>
												</div>
											</div>
											<!-- /Editor -->
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit State -->
@endif

@if(Route::is(['stock-adjustment']))
		<!-- Add Adjustment -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Adjustment</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="stock-adjustment">
									<div class="input-blocks search-form">
										<label>Product</label>
										<input type="text" class="form-control">
										<i data-feather="search" class="feather-search"></i>
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Warehouse</label>
												<select class="select">
													<option>Choose</option>
													<option>Lobar Handy</option>
													<option>Quaint Warehouse</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Reference Number</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="modal-body-table">
												<div class="table-responsive">
													<table class="table  datanew">
														<thead>
															<tr>
																<th>Product</th>
																<th>SKU</th>
																<th>Category</th>
																<th>Qty</th>
																<th>Type</th>
																<th class="no-sort">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>
																	<div class="productimgname">
																		<a href="javascript:void(0);" class="product-img stock-img">
																			<img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}" alt="product">
																		</a>
																		<a href="javascript:void(0);">Nike Jordan</a>
																	</div>												
																</td>
																<td>PT002</td>
																<td>Nike</td>
																<td>
																	<div class="product-quantity">
																		<span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
																		<input type="text" class="quntity-input" value="2">
																		<span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
																	</div>
																</td>
																<td>
																	<select class="select">
																		<option>Addition</option>
																		<option>Addition</option>
																		<option>Addition</option>
																	</select>
																</td>
																<td class="action-table-data">
																	<div class="edit-delete-action">
																		<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
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
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Responsible Person</label>
												<select class="select">
													<option>Choose</option>
													<option>Steven</option>
													<option>Gravely</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-12">
										<div class="input-blocks summer-description-box">
											<label>Notes</label>
											<textarea class="form-control"></textarea>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Create Adjustment</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Adjustment -->

		<!-- Edit Adjustment -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered stock-adjust-modal">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Adjustment</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="stock-adjustment">
									<div class="input-blocks search-form">
										<label>Product</label>
										<input type="text" class="form-control" value="Nike Jordan">
										<i data-feather="search" class="feather-search"></i>
									</div>
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Warehouse</label>
												<select class="select">
													<option>Lobar Handy</option>
													<option>Quaint Warehouse</option>
												</select>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label>Reference Number</label>
												<input type="text" value="PT002">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="modal-body-table">
												<div class="table-responsive">
													<table class="table  datanew">
														<thead>
															<tr>
																<th>Product</th>
																<th>SKU</th>
																<th>Category</th>
																<th>Qty</th>
																<th>Type</th>
																<th class="no-sort">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td>
																	<div class="productimgname">
																		<a href="javascript:void(0);" class="product-img stock-img">
																			<img src="{{ URL::asset('/build/img/products/stock-img-02.png')}}" alt="product">
																		</a>
																		<a href="javascript:void(0);">Nike Jordan</a>
																	</div>												
																</td>
																<td>PT002</td>
																<td>Nike</td>
																<td>
																	<div class="product-quantity">
																		<span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
																		<input type="text" class="quntity-input" value="2">
																		<span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
																	</div>
																</td>
																<td>
																	<select class="select">
																		<option>Addition</option>
																		<option>Addition</option>
																		<option>Addition</option>
																	</select>
																</td>
																<td class="action-table-data">
																	<div class="edit-delete-action">
																		<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-units">
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
										<div class="col-lg-12">
											<div class="input-blocks">
												<label>Responsible Person</label>
												<select class="select">
													<option>Steven</option>
													<option>Gravely</option>
												</select>
											</div>
										</div>
									</div>
									
									<div class="col-lg-12">
										<div class="input-blocks summer-description-box">
											<label>Notes</label>
											<textarea class="form-control">The Jordan brand is owned by Nike (owned by the Knight family), as, at the time, the company was building its strategy to work with athletes to launch shows that could inspire consumers.Although Jordan preferred Converse and Adidas, they simply could not match the offer Nike made. </textarea>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Adjustment -->

		<!-- View Notes -->
		<div class="modal fade" id="view-notes">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Notes</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<p>The Jordan brand is owned by Nike (owned by the Knight family), as, at the time, the company was building its strategy to work with athletes to launch shows that could inspire consumers.Although Jordan preferred Converse and Adidas, they simply could not match the offer Nike made. Jordan also signed with Nike because he loved the way they wanted to market him with the banned colored shoes. Nike promised to cover the fine Jordan would receive from the NBA.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /View Notes -->
@endif

@if(Route::is(['storage-settings']))
		<!-- Aws Config -->
		<div class="modal fade" id="aws-config">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>AWS Settings</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="storage-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">AWS Access Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label"> Bucket Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label"> Region <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label"> Base URL <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Aws Config -->
@endif

@if(Route::is(['system-settings']))
		<!-- Google Captcha -->
		<div class="modal fade" id="google-captcha">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Configure Google Captcha</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="system-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Google Recaptcha Site Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Google Recaptcha Secret Key <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Google Captcha -->

		<!-- Google Analytics -->
		<div class="modal fade" id="google-analytics">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Configure Google Analytics</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="system-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Google Analytics <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Google Analytics -->

		<!-- Google Adsense -->
		<div class="modal fade" id="google-adsense">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Configure Google Adsense Code</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="system-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Google Adsense Code <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Google Adsense -->

		<!-- Google Adsense -->
		<div class="modal fade" id="google-map">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Configure  Google Map ID</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="system-settings">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Enter Map ID <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Google Adsense -->
@endif

@if(Route::is(['tax-rates']))
		<!-- Add Tax Rates -->
		<div class="modal fade" id="add-tax">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Tax Rates</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user1" class="check" checked>
									<label for="user1" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="tax-rates">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Name <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Tax Rate % <span> *</span></label>
												<input type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Tax Rates -->

		<!-- Edit Tax Rates -->
		<div class="modal fade" id="edit-tax">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Tax Rates</h4>
								</div>
								<div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-auto me-2">
									<input type="checkbox" id="user4" class="check" checked>
									<label for="user4" class="checktoggle">	</label>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="tax-rates">
									<div class="row">
										<div class="col-lg-12">
											<div class="mb-3">
												<label class="form-label">Name <span> *</span></label>
												<input type="text" class="form-control" value="VAT">
											</div>
										</div>
										<div class="col-lg-12">
											<div class="mb-0">
												<label class="form-label">Tax Rate % <span> *</span></label>
												<input type="text" class="form-control" value="16">
											</div>
										</div>
									</div>
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Save Changes</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
@endif

@if(Route::is(['users']))
	<div class="modal fade" id="add-users">
        <div class="modal-dialog modal-dialog-centered custom-modal-two">
            <div class="modal-content">
                <div class="page-wrapper-new p-0">
                    <div class="content">
                        <div class="modal-header border-0 custom-modal-header">
                            <div class="page-title">
                                <h4>Add User</h4>
                            </div>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body custom-modal-body">
                            <form id="myForm">
                                @csrf
                                <input type="hidden" id="users_id" name="users_id">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-blocks">
                                            <label>Name</label>
                                            <input type="text" class="form-control" id="user_name" name="user_name">
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Username</label>
                                            <input type="text" class="form-control" id="uname" name="uname">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Birthdate</label>
                                        <input type="text" id="date" name="birthdate" class="form-control">
                                        <span class="form-text text-muted">dd/mm/yyyy</span>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-blocks" id="role_list">
                                            <label>Role</label>
                                            <select class="form-control" id="roles" name="roles">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Phone</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="inputGroup-sizing-default">+62</span>
                                                <input type="text" class="form-control" id="telp" name="phone">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="input-blocks">
                                            <label>Password</label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-input" id="password" name="password">
                                                <span class="fas toggle-password fa-eye-slash"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="conf">
                                        <div class="input-blocks">
                                            <label>Confirm Passworrd</label>
                                            <div class="pass-group">
                                                <input type="password" class="pass-input" id="password_confirmation" name="password_confirmation">
                                                <span class="fas toggle-password fa-eye-slash"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-12">
                                        <div class="mb-0 input-blocks">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control mb-1" id="address" name="address"></textarea>
                                            <p>Maximum 600 Charac
                                                ters</p>
                                        </div>	
                                    </div>
                                </div>
                                <div class="modal-footer-btn">
                                    <button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-submit save-users">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(Route::is(['countries']))
		<!-- Add Supplier -->
		<div class="modal fade" id="add-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Add Country</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="countries">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Country Name</label>
												<input type="text" class="form-control">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Region</label>
												<input type="text" class="form-control">
											</div>
										</div>
										
										<div class="row">
											<!-- Editor -->
											<div class="col-md-12">
												<div class="edit-add card">
													<div class="edit-add">
														<label class="form-label">Description</label>
													</div>
													<div class="card-body-list">
														<div id="summernote">Type your message</div>
													</div>
													<p>Maximum 600 Characters</p>
												</div>
											</div>
											<!-- /Editor -->
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Add Supplier -->

		<!-- Edit Supplier -->
		<div class="modal fade" id="edit-units">
			<div class="modal-dialog modal-dialog-centered custom-modal-two">
				<div class="modal-content">
					<div class="page-wrapper-new p-0">
						<div class="content">
							<div class="modal-header border-0 custom-modal-header">
								<div class="page-title">
									<h4>Edit Supplier</h4>
								</div>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body custom-modal-body">
								<form action="countries">
									<div class="row">
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Country Name</label>
												<input type="text" class="form-control" placeholder="China">
											</div>
										</div>
										<div class="col-lg-6">
											<div class="input-blocks">
												<label class="form-label">Region</label>
												<input type="text" class="form-control" placeholder="Beijing">
											</div>
										</div>
										
										<div class="row">
											<!-- Editor -->
											<div class="col-md-12">
												<div class="edit-add card">
													<div class="edit-add">
														<label class="form-label">Description</label>
				
													</div>
													<div class="card-body-list">
														<div id="summernote5">Type your message</div>
													</div>
													<p>Maximum 600 Characters</p>
												</div>
											</div>
											<!-- /Editor -->
										</div>
									</div>
									
									<div class="modal-footer-btn">
										<button type="button" class="btn btn-cancel me-2" data-bs-dismiss="modal">Cancel</button>
										<button type="submit" class="btn btn-submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Edit Supplier -->
@endif