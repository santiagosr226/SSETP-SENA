<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\ProgramaController;
use App\Http\Controllers\Admin\FuncionarioController;

Route::get('/', function () {
    return view('welcome');
});

route::get('/admin', [InicioController::class, 'index'])->name('admin');

Route::resource('programas', ProgramaController::class);

Route::resource('funcionarios', FuncionarioController::class);

