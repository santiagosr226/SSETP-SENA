<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Programa;

class ProgramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programas = Programa::orderBy('nombre')->get();
        return view('admin.programas.index', compact('programas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos del formulario
        $validated = $request->validate([
            'nivel' => [
                'required',
                'string',
                'max:50',
                'in:Técnico,Tecnólogo,Auxiliar,Operario'
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:programas,nombre', // Asegura que no haya duplicados
            ],
        ], [
            'nivel.required' => 'El campo nivel es obligatorio.',
            'nivel.max' => 'El nivel no debe exceder los 50 caracteres.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'nombre.unique' => 'Este programa ya está registrado.',
        ]);

        try {
            // 2. Crear el nuevo programa
            $programa = Programa::create([
                'nivel' => $request->nivel,
                'nombre' => $request->nombre,
            ]);

            // 3. Redirigir con mensaje de éxito
            return redirect()
                ->route('programas.index')
                ->with('success', '¡Programa creado exitosamente!');

        } catch (\Exception $e) {
            // 4. Manejar errores (opcional)
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el programa: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar el programa
        $programa = Programa::findOrFail($id);

        // Validar los datos
        $validated = $request->validate([
            'nivel' => [
                'required',
                'string',
                'max:50',
                'in:Técnico,Tecnólogo,Auxiliar,Operario'
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:programas,nombre,' . $programa->id, // Ignora el registro actual
            ],
        ], [
            'nivel.required' => 'El campo nivel es obligatorio.',
            'nivel.max' => 'El nivel no debe exceder los 50 caracteres.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'nombre.unique' => 'Este programa ya está registrado.',
        ]);

        try {
            // Actualizar el programa
            $programa->update([
                'nivel' => $request->nivel,
                'nombre' => $request->nombre,
            ]);

            return redirect()
                ->route('programas.index')
                ->with('success', '¡Programa actualizado exitosamente!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el programa: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy($id)
    {
        try {
            $programa = Programa::findOrFail($id);
            $programa->delete();

            return redirect()
                ->route('programas.index')
                ->with('success', '¡Programa eliminado exitosamente!');

        } catch (\Exception $e) {
            return redirect()
                ->route('programas.index')
                ->with('error', 'Error al eliminar el programa: ' . $e->getMessage());
        }
    }
}
