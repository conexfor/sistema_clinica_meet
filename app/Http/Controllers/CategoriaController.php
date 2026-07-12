<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\Categoria\CategoriaService;
use Illuminate\Http\Request;

/**
 * Gestión de categorías de servicios (rol admin).
 */
class CategoriaController extends Controller
{
    public function __construct(
        protected CategoriaService $categoriaService
    ) {}

    /**
     * Listado de categorías.
     */
    public function index(): \Illuminate\View\View
    {
        $categorias = $this->categoriaService->getAllCategorias();
        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Formulario de creación.
     */
    public function create(): \Illuminate\View\View
    {
        return view('admin.categorias.create');
    }

    /**
     * Guardar nueva categoría.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->categoriaService->createCategoria($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(int $id): \Illuminate\View\View
    {
        $categoria = Categoria::findOrFail($id);
        return view('admin.categorias.edit', compact('categoria'));
    }

    /**
     * Actualizar categoría.
     */
    public function update(Request $request, int $id)
    {
        $data = $this->validated($request, $id);
        $this->categoriaService->updateCategoria($id, $data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Alternar estado (activo/inactivo).
     */
    public function toggle(int $id)
    {
        $this->categoriaService->toggleStatus($id);
        return redirect()->route('admin.categorias.index')->with('success', 'Estado de la categoría actualizado.');
    }

    /**
     * Eliminar categoría.
     */
    public function destroy(int $id)
    {
        try {
            $this->categoriaService->deleteCategoria($id);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.categorias.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Reglas de validación compartidas (nombre único, ignorando el propio id en edición).
     */
    private function validated(Request $request, ?int $id = null): array
    {
        $uniqueRule = 'unique:categorias,nombre' . ($id ? ',' . $id : '');

        return $request->validate([
            'nombre'      => ['required', 'string', 'max:255', $uniqueRule],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'esta_activo' => ['nullable', 'boolean'],
        ]);
    }
}
