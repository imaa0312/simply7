<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MUserController;
use App\Http\Controllers\MRoleController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TPurchaseOrder;
use App\Http\Controllers\MProdukController;
use App\Http\Controllers\MSalesController;
use App\Http\Controllers\MStokProdukController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth
Route::get('signin-3', function () {
    return view('signin-3');
})->name('signin-3');
Route::get('/logout', [LoginController::class, 'destroy']);
Route::post('auth/login', [LoginController::class, 'store']);

Route::get('pwd', function () {
    echo bcrypt("admin");
});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return view('index');
    })->name('index');


    // Master
    Route::get('/getCategory', [MasterController::class, 'getKategori']);
    Route::get('/getSubCategory', [MasterController::class, 'getSubKategori']);
    Route::get('/getSsubCategory', [MasterController::class, 'getSsubKategori']);
    Route::get('/getSssubCategory', [MasterController::class, 'getSssubKategori']);
    Route::get('/getBrand', [MasterController::class, 'getBrand']);
    Route::get('/getSize', [MasterController::class, 'getSize']);
    Route::get('/getProvince', [MasterController::class, 'getProvince']);
    Route::get('/getCity/{id}', [MasterController::class, 'getCity']);
    Route::get('/getRole', [MUserController::class, 'getRole']);
    Route::get('/getManager', [MasterController::class, 'getStoreManager']);
    Route::get('/getExpenseCategory', [MasterController::class, 'getExpenseCategory']);
    Route::get('/getProduct/{id}', [MSalesController::class, 'getProduct']);
    Route::get('/getSupp', [MSalesController::class, 'getSupp']);
    Route::get('/getRef', [TPurchaseOrder::class, 'getRef']);

    Route::get('/roles', [MRoleController::class, 'index'])->name('roles');  
    Route::get('/roles-datatables', [MRoleController::class, 'rolesDatatables'])->name('roles-datatables');  
    Route::get('/edit-roles/{id}', [MRoleController::class, 'editRoles'])->name('roles-edit');
    Route::post('/save-roles', [MRoleController::class, 'storeRoles'])->name('roles-save');

    Route::get('/suppliers', [MasterController::class, 'supplier'])->name('suppliers');  
    Route::get('/suppliers-datatables', [MasterController::class, 'supplierDatatables'])->name('suppliers-datatables');  
    Route::get('/edit-supplier/{id}', [MasterController::class, 'editSupplier'])->name('supplier-edit');
    Route::post('/save-supplier', [MasterController::class, 'storeSupplier'])->name('supplier-save');
    Route::get('/delete-supplier/{id}', [MasterController::class, 'deleteSupplier'])->name('supplier-delete');
    Route::get('/restore-supplier/{id}', [MasterController::class, 'restoreSupplier'])->name('supplier-restore');

    Route::get('/warehouse', [MasterController::class, 'warehouse'])->name('warehouse');
    Route::get('/warehouse-datatables', [MasterController::class, 'warehouseDatatables'])->name('warehouse-datatables');  
    Route::get('/edit-warehouse/{id}', [MasterController::class, 'editWarehouse'])->name('warehouse-edit');
    Route::post('/save-warehouse', [MasterController::class, 'storeWarehouse'])->name('warehouse-save');
    Route::get('/delete-warehouse/{id}', [MasterController::class, 'deleteWarehouse'])->name('warehouse-delete');
    Route::get('/restore-warehouse/{id}', [MasterController::class, 'restoreWarehouse'])->name('warehouse-restore'); 

    Route::get('/category-list', [MasterController::class, 'category'])->name('category-list');
    Route::get('/category-datatables', [MasterController::class, 'categoryDatatables'])->name('category-datatables');
    Route::get('/edit-category-list/{id}', [MasterController::class, 'editKategori'])->name('category-edit');
    Route::post('/save-category-list', [MasterController::class, 'storeKategori'])->name('category-save');
    Route::get('/delete-category-list/{id}', [MasterController::class, 'deleteKategori'])->name('category-delete');
    Route::get('/restore-category-list/{id}', [MasterController::class, 'restoreKategori'])->name('category-restore');

    Route::get('/subcategory-list', [MasterController::class, 'subcategory'])->name('subcategory-list');
    Route::get('/subcategory-datatables', [MasterController::class, 'subcategoryDatatables'])->name('subcategory-datatables');
    Route::get('/edit-subcategory-list/{id}', [MasterController::class, 'editSubKategori'])->name('subcategory-edit');
    Route::post('/save-subcategory-list', [MasterController::class, 'storeSubKategori'])->name('subcategory-save');
    Route::get('/delete-subcategory-list/{id}', [MasterController::class, 'deleteSubKategori'])->name('subcategory-delete');
    Route::get('/restore-subcategory-list/{id}', [MasterController::class, 'restoreSubKategori'])->name('subcategory-restore');

    Route::get('/ssubcategory-list', [MasterController::class, 'ssubcategory'])->name('ssubcategory-list');
    Route::get('/ssubcategory-datatables', [MasterController::class, 'ssubcategoryDatatables'])->name('ssubcategory-datatables');
    Route::get('/edit-ssubcategory-list/{id}', [MasterController::class, 'editSsubKategori'])->name('ssubcategory-edit');
    Route::post('/save-ssubcategory-list', [MasterController::class, 'storeSsubKategori'])->name('ssubcategory-save');
    Route::get('/delete-ssubcategory-list/{id}', [MasterController::class, 'deleteSsubKategori'])->name('ssubcategory-delete');
    Route::get('/restore-ssubcategory-list/{id}', [MasterController::class, 'restoreSsubKategori'])->name('ssubcategory-restore');

    Route::get('/sssubcategory-list', [MasterController::class, 'sssubcategory'])->name('sssubcategory-list');
    Route::get('/sssubcategory-datatables', [MasterController::class, 'sssubcategoryDatatables'])->name('sssubcategory-datatables');
    Route::get('/edit-sssubcategory-list/{id}', [MasterController::class, 'editSssubKategori'])->name('sssubcategory-edit');
    Route::post('/save-sssubcategory-list', [MasterController::class, 'storeSssubKategori'])->name('sssubcategory-save');
    Route::get('/delete-sssubcategory-list/{id}', [MasterController::class, 'deleteSssubKategori'])->name('sssubcategory-delete');
    Route::get('/restore-sssubcategory-list/{id}', [MasterController::class, 'restoreSssubKategori'])->name('sssubcategory-restore');

    Route::get('/brand-list', [MasterController::class, 'brand'])->name('brand-list');
    Route::get('/brand-datatables', [MasterController::class, 'brandDatatables'])->name('brand-datatables');  
    Route::get('/edit-brand/{id}', [MasterController::class, 'editBrand'])->name('brand-edit');
    Route::post('/save-brand', [MasterController::class, 'storeBrand'])->name('brand-save');
    Route::get('/delete-brand/{id}', [MasterController::class, 'deleteBrand'])->name('brand-delete');
    Route::get('/restore-brand/{id}', [MasterController::class, 'restoreBrand'])->name('brand-restore'); 

    Route::get('/size', [MasterController::class, 'size'])->name('size');
    Route::get('/size-datatables', [MasterController::class, 'sizeDatatables'])->name('size-datatables');  
    Route::get('/edit-size/{id}', [MasterController::class, 'editSize'])->name('size-edit');
    Route::post('/save-size', [MasterController::class, 'storeSize'])->name('size-save');
    Route::get('/delete-size/{id}', [MasterController::class, 'deleteSize'])->name('size-delete');
    Route::get('/restore-size/{id}', [MasterController::class, 'restoreSize'])->name('size-restore'); 

    Route::get('/store-list', [MasterController::class, 'store'])->name('store-list'); 
    Route::get('/store-datatables', [MasterController::class, 'storeDatatables'])->name('store-datatables');  
    Route::get('/edit-store/{id}', [MasterController::class, 'editStore'])->name('store-edit');
    Route::post('/save-store', [MasterController::class, 'storeStore'])->name('store-save');
    Route::get('/delete-store/{id}', [MasterController::class, 'deleteStore'])->name('store-delete');
    Route::get('/restore-store/{id}', [MasterController::class, 'restoreStore'])->name('store-restore'); 

    Route::get('/expense-category', [MasterController::class, 'expenseCategory'])->name('expense-category'); 
    Route::get('/expense-category-datatables', [MasterController::class, 'expenseCategoryDatatables'])->name('expense-category-datatables');  
    Route::get('/edit-expense-category/{id}', [MasterController::class, 'editExpenseCategory'])->name('expense-category-edit');
    Route::post('/save-expense-category', [MasterController::class, 'storeExpenseCategory'])->name('expense-category-save');
    Route::get('/delete-expense-category/{id}', [MasterController::class, 'deleteExpenseCategory'])->name('expense-category-delete');
    Route::get('/restore-expense-category/{id}', [MasterController::class, 'restoreExpenseCategory'])->name('expense-category-restore');  

    Route::get('/expense-list', [FinanceController::class, 'expense'])->name('expense-list'); 
    Route::get('/expense-datatables', [FinanceController::class, 'expenseDatatables'])->name('expense-datatables');  
    Route::get('/edit-expense/{id}', [FinanceController::class, 'editExpense'])->name('expense-edit');
    Route::post('/save-expense', [FinanceController::class, 'storeExpense'])->name('expense-save');
    Route::get('/delete-expense/{id}', [FinanceController::class, 'deleteExpense'])->name('expense-delete');


    // User
    Route::get('/users', [MUserController::class, 'index'])->name('users');
    Route::get('/users-datatables', [MUserController::class, 'usersDatatables'])->name('users-datatables');  
    Route::get('/edit-users/{id}', [MUserController::class, 'editUsers'])->name('users-edit');
    Route::post('/save-users', [MUserController::class, 'storeUsers'])->name('users-save');
    Route::get('/delete-users/{id}', [MUserController::class, 'deleteUsers'])->name('users-delete');
    Route::get('/restore-users/{id}', [MUserController::class, 'restoreUsers'])->name('users-restore'); 

    Route::get('/customers', [MUserController::class, 'customers'])->name('customers');  
    Route::get('/customers-datatables', [MUserController::class, 'customersDatatables'])->name('customers-datatables');  
    Route::get('/edit-customers/{id}', [MUserController::class, 'editCustomers'])->name('customers-edit');
    Route::post('/save-customers', [MUserController::class, 'storeCustomers'])->name('customers-save');
    Route::get('/delete-customers/{id}', [MUserController::class, 'deleteCustomers'])->name('customers-delete');
    Route::get('/restore-customers/{id}', [MUserController::class, 'restoreCustomers'])->name('customers-restore'); 

    Route::get('/product-list', [MProdukController::class, 'product'])->name('product-list');
    Route::get('/product-datatables', [MProdukController::class, 'productDatatables'])->name('product-datatables'); 
    Route::get('/edit-product/{id}', [MProdukController::class, 'editProduct'])->name('product-edit'); 
    Route::post('/save-product-images', [MProdukController::class, 'uploadImages'])->name('upload-images');
    Route::get('/del-image-product/{id}', [MProdukController::class, 'delImages'])->name('del-image-product');
    Route::post('/save-product', [MProdukController::class, 'storeProduct'])->name('save-product');
    Route::get('/delete-product/{id}', [MProdukController::class, 'deleteProduct'])->name('product-delete');

    Route::get('/purchase-order', [TPurchaseOrder::class, 'purchaseOrder'])->name('purchase-order'); 
    Route::get('/po-producttemp-datatables/{id}', [TPurchaseOrder::class, 'poProductTempDatatable'])->name('po-producttemp-datatables'); 
    Route::get('/po-product-datatables/{id}', [TPurchaseOrder::class, 'poProductDatatable'])->name('po-product-datatables'); 
    Route::get('/po-datatables', [TPurchaseOrder::class, 'poDatatables'])->name('po-datatables'); 
    Route::post('/save-po-temp', [TPurchaseOrder::class, 'poTemp'])->name('save-po-temp'); 
    Route::get('/del-po-temp/{id}', [TPurchaseOrder::class, 'poDestroyTemp'])->name('del-po-temp'); 
    Route::post('/save-po-product', [TPurchaseOrder::class, 'poProductTemp'])->name('save-po-product'); 
    Route::post('/save-po', [TPurchaseOrder::class, 'poStore'])->name('save-po'); 
    Route::get('/edit-po/{id}', [TPurchaseOrder::class, 'poEdit'])->name('edit-po'); 
    Route::get('/del-po/{id}', [TPurchaseOrder::class, 'poDel'])->name('del-po'); 

    Route::get('/purchase-received', [TPurchaseOrder::class, 'purchaseReceived'])->name('purchase-received'); 
    Route::get('/pr-datatables', [TPurchaseOrder::class, 'prDatatables'])->name('pr-datatables'); 
    Route::get('/pr-barcode/{id}/{br}', [TPurchaseOrder::class, 'prBarcode'])->name('pr-barcode'); 
    Route::get('/pr-product-datatables/{id}', [TPurchaseOrder::class, 'prProduct'])->name('pr-product-datatables');
    Route::post('/save-pr', [TPurchaseOrder::class, 'prStore'])->name('save-pr');  
    Route::get('/add-pr', [TPurchaseOrder::class, 'prAdd'])->name('add-pr');  
    Route::get('/edit-pr/{id}', [TPurchaseOrder::class, 'prEdit'])->name('edit-pr');  

    Route::get('/pos', [MSalesController::class, 'index'])->name('pos');  
    Route::get('/addtocart/{id}', [MSalesController::class, 'addtocart']);
    Route::get('/loadcart/{id}', [MSalesController::class, 'load_cart']);
    Route::get('/pos-qty/{id}/{desc}', [MSalesController::class, 'cartQty'])->name('pos-qty');  
    Route::get('/fill-qty/{id}/{qty}', [MSalesController::class, 'fillQty'])->name('fill-qty');  
    Route::get('/pos-del/{id}', [MSalesController::class, 'posDel'])->name('pos-del');  
    Route::get('/pos-void/{id}', [MSalesController::class, 'posVoid'])->name('pos-void');  
    Route::get('/pos-cust', [MSalesController::class, 'posCust'])->name('pos-cust');  
    Route::get('/pos-prod', [MSalesController::class, 'posProd'])->name('pos-prod');  

    Route::get('/stock-transfer', [MStokProdukController::class, 'stockTransfer'])->name('stock-transfer'); 
    Route::get('/stock-transfer-datatables', [MStokProdukController::class, 'stockTransferDatatables'])->name('stock-transfer-datatables'); 
    Route::get('/ts-getDest/{id}', [MStokProdukController::class, 'getDest'])->name('ts-getDest'); 
    Route::get('/ts-getMax/{id}/{asal}', [MStokProdukController::class, 'getMax'])->name('ts-getMax'); 
    Route::get('/ts-prod/{id}', [MStokProdukController::class, 'getProduct'])->name('ts-prod'); 
    Route::post('/ts-save', [MStokProdukController::class, 'transferStore'])->name('ts-save'); 
    Route::get('/ts-edit/{id}', [MStokProdukController::class, 'editTransfer'])->name('ts-edit'); 




    // SHOPEEEEE //
    Route::get('get-shop-info', [ShopeeController::class, 'getShopInfo'])->name('get-shop-info');
});









Route::get('/low-stocks', function () {
    return view('low-stocks');
})->name('low-stocks');

Route::get('/product-details', function () {
    return view('product-details');
})->name('product-details');

Route::get('/edit-product', function () {
    return view('edit-product');
})->name('edit-product');   

Route::get('/manage-stocks', function () {                         
    return view('manage-stocks');
})->name('manage-stocks');      

Route::get('/stock-adjustment', function () {                         
    return view('stock-adjustment');
})->name('stock-adjustment');     

Route::get('/purchase-report', function () {                         
    return view('purchase-report');
})->name('purchase-report'); 


Route::get('/sales-list', function () {                         
    return view('sales-list');
})->name('sales-list'); 

Route::get('/invoice-report', function () {                         
    return view('invoice-report');
})->name('invoice-report'); 

Route::get('/sales-returns', function () {                         
    return view('sales-returns');
})->name('sales-returns'); 

Route::get('/coupons', function () {                         
    return view('coupons');
})->name('coupons');  

Route::get('/sales-report', function () {
    return view('sales-report');
})->name('sales-report');

Route::get('/purchase-report', function () {
    return view('purchase-report');
})->name('purchase-report');

Route::get('/inventory-report', function () {
    return view('inventory-report');
})->name('inventory-report');

Route::get('/invoice-report', function () {
    return view('invoice-report');
})->name('invoice-report');

Route::get('/supplier-report', function () {
    return view('supplier-report');
})->name('supplier-report');

Route::get('/customer-report', function () {
    return view('customer-report');
})->name('customer-report');

Route::get('/expense-report', function () {
    return view('expense-report');
})->name('expense-report');

Route::get('/income-report', function () {
    return view('income-report');
})->name('income-report');

Route::get('/tax-reports', function () {
    return view('tax-reports');
})->name('tax-reports');

Route::get('/profit-and-loss', function () {
    return view('profit-and-loss');
})->name('profit-and-loss');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/blank-page', function () {
    return view('blank-page');
})->name('blank-page');

Route::get('/countries', function () {
    return view('countries');
})->name('countries');

Route::get('/states', function () {
    return view('states');
})->name('states');

Route::get('/reset-password-3', function () {
    return view('reset-password-3');
})->name('reset-password-3');

Route::get('/reset-password-2', function () {
    return view('reset-password-2');
})->name('reset-password-2');

Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

Route::get('/forgot-password-3', function () {
    return view('forgot-password-3');
})->name('forgot-password-3');

Route::get('/forgot-password-2', function () {
    return view('forgot-password-2');
})->name('forgot-password-2');

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->name('forgot-password');

Route::get('/register-3', function () {
    return view('register-3');
})->name('register-3');

Route::get('/register-2', function () {
    return view('register-2');
})->name('register-2');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/signin-3', function () {
    return view('signin-3');
})->name('signin-3');

Route::get('/signin-2', function () {
    return view('signin-2');
})->name('signin-2');

Route::get('/signin', function () {
    return view('signin');
})->name('signin');

Route::get('/bank-settings-grid', function () {
    return view('bank-settings-grid');
})->name('bank-settings-grid');     

Route::get('/tax-rates', function () {
    return view('tax-rates');
})->name('tax-rates');   