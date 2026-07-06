<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WebController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\IncomeTypeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProductController;

Route::get('optimize', function(){
	Artisan::call('optimize:clear');
});

Route::get('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('login', [AuthController::class, 'check'])->name('auth.check');
Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware('auth')->group(function(){

	Route::get('/',[WebController::class, 'index']);
	
	Route::get('cash_report',[WebController::class, 'cash_report'])->name('cash_report');
	
	Route::get('calendar',[WebController::class, 'calendar'])->name('calendar');

	
	Route::get('events',[WebController::class, 'events'])->name('events');
	
	Route::get('cash_flow',[WebController::class, 'cash_flow'])->name('cash_flow');

	Route::resource('locations', LocationController::class);
	Route::resource('sublocations', App\Http\Controllers\SublocationController::class);
	
	Route::resource('event_types', EventTypeController::class);
	
	Route::resource('packages', PackageController::class);
	
	Route::resource('extras', ExtraController::class);

	Route::resource('clients', ClientController::class);
	
	Route::resource('providers', ProviderController::class);
	
	Route::resource('employees', EmployeeController::class);
	
	Route::resource('commissions', CommissionController::class);
	
	Route::resource('income_types', IncomeTypeController::class);
	
	Route::resource('expense_types', ExpenseTypeController::class);

	Route::resource('product_types', ProductTypeController::class);
	
	Route::resource('payment_schedules', PaymentScheduleController::class);
	
	Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
	Route::resource('quotations', QuotationController::class);
	
	Route::get('contracts/charges', [ContractController::class, 'charges'])->name('contracts.charges');
	Route::post('contracts/{contract}/payment', [ContractController::class, 'payment'])->name('contracts.payment');
	Route::get('contracts/{contract}/payments', [ContractController::class, 'payments'])->name('contracts.payments');
	Route::post('contracts/{contract}/extra', [ContractController::class, 'extra'])->name('contracts.extra');
	Route::get('contracts/{contract}/employees', [ContractController::class, 'employees'])->name('contracts.employees');
	Route::post('contracts/{contract}/employee', [ContractController::class, 'employee'])->name('contracts.employee');
	Route::get('contracts/{contract}/schedules', [ContractController::class, 'schedules'])->name('contracts.schedules');
	Route::post('contracts/{contract}/schedule', [ContractController::class, 'schedule'])->name('contracts.schedule');
	Route::get('contracts/{contract}/total', [ContractController::class, 'total'])->name('contracts.total');
	Route::post('contracts/{contract}/total', [ContractController::class, 'updateTotal'])->name('contracts.updateTotal');
	Route::get('contracts/{contract}/pdf', [ContractController::class, 'pdf'])->name('contracts.pdf');
	Route::get('contracts/{contract}/pdf2', [ContractController::class, 'pdf2'])->name('contracts.pdf2');
	Route::get('contracts/{contract}/pdf3', [ContractController::class, 'pdf3'])->name('contracts.pdf3');
	Route::resource('contracts', ContractController::class);

	Route::resource('incomes', IncomeController::class);

	Route::get('purchases/report', [PurchaseController::class, 'report'])->name('purchases.report');
	Route::resource('purchases', PurchaseController::class);
	
	Route::get('expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
	Route::resource('expenses', ExpenseController::class);
	
	Route::get('products/{product}/movements', [ProductController::class, 'movements'])->name('products.movements');
	Route::post('products/{product}/decrement', [ProductController::class, 'decrement'])->name('products.decrement');
	Route::post('products/{product}/increment', [ProductController::class, 'increment'])->name('products.increment');
	Route::get('products/pdf', [ProductController::class, 'pdf'])->name('products.pdf');
	Route::resource('products', ProductController::class);


	Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
	Route::post('settings/password', [SettingController::class, 'password'])->name('settings.password');
	Route::post('settings/pin', [SettingController::class, 'pin'])->name('settings.pin');

	Route::get('help',[WebController::class, 'help'])->name('help');

	Route::get('search-dni', [WebController::class, 'searchDni'])->name('search.dni');
	Route::get('search-ruc', [WebController::class, 'searchRuc'])->name('search.ruc');

	Route::get('export/{module}/{format}', [App\Http\Controllers\ExportController::class, 'export'])->name('export');

});
