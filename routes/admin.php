<?php

use Azuriom\Plugin\Draw\Controllers\Admin\AdminController;
use Azuriom\Plugin\Draw\Controllers\Admin\RewardsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "web" middleware group and your plugin name
| as prefix. Now create something great!
|
*/

Route::get('/', [AdminController::class, 'index'])->name('index');
Route::get('/{id}/edit', [AdminController::class, 'edit'])->name('draws.edit');
Route::post('/{id}/edit/submit', [AdminController::class, 'edit_submit'])->name('draws.edit.submit');
Route::get('/{id}/entries', [AdminController::class, 'entries'])->name('draws.entries');
Route::post('/{id}/close', [AdminController::class, 'close'])->name('draws.close');
Route::post('/{id}/stop', [AdminController::class, 'stop'])->name('draws.stop');
Route::post('/{id}/replay', [AdminController::class, 'replay'])->name('draws.replay');
Route::post('/{id}/delete', [AdminController::class, 'delete'])->name('draws.delete');
Route::get('/add', [AdminController::class, 'add'])->name('draws.add');
Route::post('/add/submit', [AdminController::class, 'add_submit'])->name('draws.add.submit');

Route::get('/rewards', [RewardsController::class, 'index'])->name('rewards');
Route::get('/reward/add', [RewardsController::class, 'add'])->name('rewards.add');
Route::post('/reward/add/submit', [RewardsController::class, 'add_submit'])->name('rewards.add.submit');
Route::get('/reward/{id}/edit', [RewardsController::class, 'edit'])->name('rewards.edit');
Route::post('/reward/{id}/edit/submit', [RewardsController::class, 'edit_submit'])->name('rewards.edit.submit');
Route::post('/reward/{id}/delete', [RewardsController::class, 'delete'])->name('rewards.delete');