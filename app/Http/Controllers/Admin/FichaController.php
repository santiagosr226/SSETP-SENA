<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ficha;
use App\Models\Funcionario;
use App\Models\Programa;
use Illuminate\Http\Request;

class FichaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
    {
        $search = $request->input('search');
        
        $fichas = Ficha::query()
            ->with(['programa', 'instructor'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhere('estado', 'like', "%{$search}%")
                      ->orWhere('modalidad', 'like', "%{$search}%")
                      ->orWhere('jornada', 'like', "%{$search}%")
                      ->orWhereHas('programa', function ($q2) use ($search) {
                          $q2->where('nombre', 'like', "%{$search}%");
                      })
                      ->orWhereHas('instructor', function ($q3) use ($search) {
                          $q3->where('nombre', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.fichas.index', compact('fichas', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $programas = Programa::all();
        $instructores = Funcionario::where('rol', 'instructor')->get();
        return view('admin.fichas.create', compact('programas', 'instructores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
