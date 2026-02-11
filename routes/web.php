<?php

use App\Http\Livewire\CreateCustomer;
use App\Http\Livewire\CreatePaymentLink;
use App\Http\Livewire\CreatePlan;
use App\Http\Livewire\CreateSubscription;
use App\Http\Livewire\ManageCards;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customers/create', CreateCustomer::class)->name('customers.create');
Route::get('/plans/create', CreatePlan::class)->name('plans.create');
Route::get('/subscriptions/create', CreateSubscription::class)->name('subscriptions.create');
Route::get('/payment-links/create', CreatePaymentLink::class)->name('payment-links.create');
Route::get('/cards', ManageCards::class)->name('cards.index');
