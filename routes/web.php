<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\MasterController;

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
Route::get('index', [CustomAuthController::class, 'dashboard']); 
Route::get('signin', [CustomAuthController::class, 'index'])->name('signin');
Route::post('custom-login', [CustomAuthController::class, 'customSignin'])->name('signin.custom'); 
Route::get('register', [CustomAuthController::class, 'registration'])->name('register');
Route::post('custom-register', [CustomAuthController::class, 'customRegister'])->name('register.custom'); 
Route::get('signout', [CustomAuthController::class, 'signOut'])->name('signout');

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/index', function () {
    return view('index');
})->name('index');

Route::get('/product-list', function () {
    return view('product-list');
})->name('product-list');

Route::get('/low-stocks', function () {
    return view('low-stocks');
})->name('low-stocks');






 //kategori
//  Route::get('produk/kategori', [MasterController::class, 'index']);
//  Route::get('kategori/api/kategori','MKategoriController@apiKategori');
 Route::get('produk/kategori-delete/{id}','MKategoriController@destroy');

 Route::resource('produk/sub-kategori','MSubKategoriController');
//  Route::get('subkat/api/subkat','MSubKategoriController@apiSubkat');
 Route::get('produk/sub-kategori-delete/{id}','MSubKategoriController@destroy');

 //merk
 Route::resource('produk/merek','MMerekProdukController');
//  Route::get('merek/api/merek','MMerekProdukController@apiMerek');
 Route::get('produk/merek-delete/{id}','MMerekProdukController@destroy');







Route::get('/category-list', [MasterController::class, 'category'])->name('category-list');
Route::get('/edit-category-list/{id}', [MasterController::class, 'edit'])->name('category-edit');

Route::get('/sub-categories', function () {
    return view('sub-categories');
})->name('sub-categories');

Route::get('/brand-list', function () {
    return view('brand-list');
})->name('brand-list');

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

Route::get('/stock-transfer', function () {                         
    return view('stock-transfer');
})->name('stock-transfer'); 

Route::get('/purchase-list', function () {                         
    return view('purchase-list');
})->name('purchase-list'); 

Route::get('/purchase-order-report', function () {                         
    return view('purchase-order-report');
})->name('purchase-order-report'); 

Route::get('/purchase-returns', function () {                         
    return view('purchase-returns');
})->name('purchase-returns'); 

Route::get('/expense-list', function () {                         
    return view('expense-list');
})->name('expense-list'); 

Route::get('/expense-category', function () {                         
    return view('expense-category');
})->name('expense-category');     

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

Route::get('/pos', function () {                         
    return view('pos');
})->name('pos');  

Route::get('/coupons', function () {                         
    return view('coupons');
})->name('coupons');  

Route::get('/customers', function () {                         
    return view('customers');
})->name('customers');  

Route::get('/suppliers', function () {                         
    return view('suppliers');
})->name('suppliers');  

Route::get('/store-list', function () {                         
    return view('store-list');
})->name('store-list');  

Route::get('/warehouse', function () {                         
    return view('warehouse');
})->name('warehouse');  


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