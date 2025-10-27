<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', \App\Livewire\Home::class);

Route::prefix('cp')->middleware(['auth'])->group(function () {

    Volt::route('/', 'users-bak.index')->name('home');

    Volt::route('/product', 'product.index')->name('product.index');
    Volt::route('/product/create', 'product.create')->name('product.create');
    Volt::route('/product/{product}/edit', 'product.edit')->name('product.edit');
    Volt::route('/product/import', 'product.import')->name('product.import');

    Volt::route('/order', 'order.index')->name('order.index');
    Volt::route('/order/create', 'order.create')->name('order.create');
    Volt::route('/order/{order}/edit', 'order.edit')->name('order.edit');
    Volt::route('/order/import', 'order.import')->name('order.import');

    Volt::route('/users', 'users.index')->name('users.index');
    Volt::route('/users/create', 'users.create')->name('users.create');
    Volt::route('/users/{user}/edit', 'users.edit')->name('users.edit');
    Volt::route('/users/profile', 'users.profile')->name('users.profile');

    Volt::route('/permissions', 'permissions.index')->name('permissions.index');
    Volt::route('/permissions/create', 'permissions.create')->name('permissions.create');
    Volt::route('/permissions/{permission}/edit', 'permissions.edit')->name('permissions.edit');
    Volt::route('/permissions/import', 'permissions.import')->name('permissions.import');

    Volt::route('/roles', 'roles.index')->name('roles.index');
    Volt::route('/roles/create', 'roles.create')->name('roles.create');
    Volt::route('/roles/{role}/edit', 'roles.edit')->name('roles.edit');
    Volt::route('/roles/import', 'roles.import')->name('roles.import');

    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('users.logout');
});
