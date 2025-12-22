<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el término de búsqueda
        $search = $request->input('search');
        
        // Iniciar la consulta
        $query = Funcionario::query();
        
        // Aplicar búsqueda si existe
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('correo', 'LIKE', "%{$search}%")
                  ->orWhere('telefono', 'LIKE', "%{$search}%")
                  ->orWhere('rol', 'LIKE', "%{$search}%");
            });
        }
        
        // Ordenar por nombre y aplicar paginación
        $funcionarios = $query->orderBy('nombre')->paginate(15);
        
        // Mantener el parámetro de búsqueda en los links de paginación
        if ($search) {
            $funcionarios->appends(['search' => $search]);
        }
        
        return view('admin.funcionarios.index', compact('funcionarios', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos del formulario
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'correo' => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:funcionarios,correo'
            ],
            'telefono' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\d\s\+\-\(\)]+$/'
            ],
            'rol' => [
                'required',
                'string',
                'in:administrador,coordinador,instructor'
            ],
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.max' => 'El correo no debe exceder los 100 caracteres.',
            'correo.unique' => 'Este correo ya está registrado.',
            'telefono.required' => 'El campo teléfono es obligatorio.',
            'telefono.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'telefono.regex' => 'Formato de teléfono inválido.',
            'rol.required' => 'El campo rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        try {
            // 2. Crear el nuevo funcionario
            $funcionario = Funcionario::create([
                'nombre' => ucwords(strtolower($request->nombre)),
                'correo' => strtolower($request->correo),
                'telefono' => $request->telefono,
                'rol' => $request->rol,
                'password' => bcrypt('Sena2024'),
            ]);

            // 3. Redirigir con mensaje de éxito
            return redirect()
                ->route('funcionarios.index')
                ->with('success', '¡Funcionario creado exitosamente!');

        } catch (\Exception $e) {
            // 4. Manejar errores
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el funcionario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Puedes implementar esto si necesitas una vista individual
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar el funcionario
        $funcionario = Funcionario::findOrFail($id);

        // Validar los datos
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'correo' => [
                'required',
                'string',
                'email',
                'max:100',
                'unique:funcionarios,correo,' . $funcionario->id
            ],
            'telefono' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\d\s\+\-\(\)]+$/'
            ],
            'rol' => [
                'required',
                'string',
                'in:administrador,coordinador,instructor'
            ],
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.max' => 'El correo no debe exceder los 100 caracteres.',
            'correo.unique' => 'Este correo ya está registrado.',
            'telefono.required' => 'El campo teléfono es obligatorio.',
            'telefono.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'telefono.regex' => 'Formato de teléfono inválido.',
            'rol.required' => 'El campo rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        try {
            // Actualizar el funcionario
            $funcionario->update([
                'nombre' => ucwords(strtolower($request->nombre)),
                'correo' => strtolower($request->correo),
                'telefono' => $request->telefono,
                'rol' => $request->rol,
            ]);

            return redirect()
                ->route('funcionarios.index')
                ->with('success', '¡Funcionario actualizado exitosamente!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el funcionario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $funcionario = Funcionario::findOrFail($id);
            $funcionario->delete();

            return redirect()
                ->route('funcionarios.index')
                ->with('success', '¡Funcionario eliminado exitosamente!');

        } catch (\Exception $e) {
            return redirect()
                ->route('funcionarios.index')
                ->with('error', 'Error al eliminar el funcionario: ' . $e->getMessage());
        }
    }
}