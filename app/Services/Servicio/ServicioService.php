<?php

namespace App\Services\Servicio;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ServicioService
 * Clase encargada de la lógica de negocio para la gestión de servicios de la clínica.
 */
class ServicioService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    /**
     * Obtener todos los servicios.
     */
    public function getAllServicios(): Collection
    {
        return Servicio::with(['modalidades', 'categoria'])->withCount('especialistas')->get();
    }

    /**
     * Obtener solo los servicios activos.
     */
    public function getActiveServicios(): Collection
    {
        return Servicio::active()->with(['modalidades', 'categoria'])->withCount('especialistas')->get();
    }

    /**
     * Crear un nuevo servicio.
     */
    public function createServicio(array $data): Servicio
    {
        return DB::transaction(function () use ($data) {
            $servicio = Servicio::create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'duracion' => $data['duracion'],
                'categoria_id' => $data['categoria_id'],
                'categoria' => $this->resolveCategoriaNombre($data['categoria_id']),
            ]);

            if (isset($data['modalidades'])) {
                $servicio->modalidades()->sync($data['modalidades']);
            }

            return $servicio;
        });
    }

    /**
     * Actualizar un servicio existente.
     */
    public function updateServicio(int $id, array $data): Servicio
    {
        return DB::transaction(function () use ($id, $data) {
            $servicio = Servicio::findOrFail($id);
            $precioAnterior = (float) $servicio->precio;

            $servicio->update([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'precio' => $data['precio'],
                'duracion' => $data['duracion'],
                'categoria_id' => $data['categoria_id'],
                'categoria' => $this->resolveCategoriaNombre($data['categoria_id']),
            ]);

            if (isset($data['modalidades'])) {
                $servicio->modalidades()->sync($data['modalidades']);
            }

            // Auditar cambios de precio (acción sensible).
            $precioNuevo = (float) $data['precio'];
            if ($precioAnterior !== $precioNuevo) {
                $this->audit->log(
                    'servicio.precio',
                    'Cambió el precio de "' . $servicio->nombre . '" de S/ ' . number_format($precioAnterior, 2) . ' a S/ ' . number_format($precioNuevo, 2) . '.',
                    [
                        'categoria' => Auditoria::CAT_FINANZAS,
                        'metadata'  => ['servicio_id' => $servicio->id, 'anterior' => $precioAnterior, 'nuevo' => $precioNuevo],
                    ]
                );
            }

            return $servicio;
        });
    }

    /**
     * Resolver el nombre de la categoría para mantener sincronizada la columna
     * legacy "categoria" (string) a partir del categoria_id seleccionado.
     */
    private function resolveCategoriaNombre(int $categoriaId): ?string
    {
        return Categoria::whereKey($categoriaId)->value('nombre');
    }

    /**
     * Alternar el estado de activación de un servicio.
     */
    public function toggleStatus(int $id): Servicio
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::findOrFail($id);
            $servicio->update(['esta_activo' => !$servicio->esta_activo]);
            return $servicio;
        });
    }

    /**
     * Eliminar un servicio.
     */
    public function deleteServicio(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::findOrFail($id);
            // Podríamos verificar si tiene citas asociadas antes de eliminar
            // Por ahora eliminación directa
            return $servicio->delete();
        });
    }
}
