<?php

namespace App\Services\Categoria;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CategoriaService
 * Lógica de negocio para la gestión de categorías de servicios.
 */
class CategoriaService
{
    /**
     * Obtener todas las categorías con el conteo de servicios asociados.
     */
    public function getAllCategorias(): Collection
    {
        return Categoria::withCount('servicios')->orderBy('nombre')->get();
    }

    /**
     * Obtener solo las categorías activas.
     */
    public function getActiveCategorias(): Collection
    {
        return Categoria::active()->orderBy('nombre')->get();
    }

    /**
     * Crear una nueva categoría.
     */
    public function createCategoria(array $data): Categoria
    {
        return Categoria::create([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'esta_activo' => $data['esta_activo'] ?? true,
        ]);
    }

    /**
     * Actualizar una categoría existente.
     */
    public function updateCategoria(int $id, array $data): Categoria
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'esta_activo' => $data['esta_activo'] ?? $categoria->esta_activo,
        ]);

        return $categoria;
    }

    /**
     * Alternar el estado de activación de una categoría.
     */
    public function toggleStatus(int $id): Categoria
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['esta_activo' => ! $categoria->esta_activo]);

        return $categoria;
    }

    /**
     * Eliminar una categoría. No se permite si tiene servicios asociados.
     *
     * @throws \RuntimeException
     */
    public function deleteCategoria(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $categoria = Categoria::withCount('servicios')->findOrFail($id);

            if ($categoria->servicios_count > 0) {
                throw new \RuntimeException('No se puede eliminar una categoría con servicios asociados.');
            }

            return (bool) $categoria->delete();
        });
    }
}
