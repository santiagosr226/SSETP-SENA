<?php

use App\Http\Controllers\Admin\FichaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\ProgramaController;
use App\Http\Controllers\Admin\FuncionarioController;

Route::redirect('/', '/admin', 301);

route::get('/admin', [InicioController::class, 'index'])->name('admin');

Route::resource('programas', ProgramaController::class);

Route::resource('funcionarios', FuncionarioController::class);

Route::resource('fichas', FichaController::class);

