<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Especialista\EspecialistaService;
use App\Models\NotaEspecialista;

class NotaController extends Controller
{
    public function __construct(
        protected EspecialistaService $especialistaService
    ) {}

    /**
     * Store a newly created note.
     */
    public function store(Request $request)
    {
        $especialista = $request->user()->especialista;

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        $this->especialistaService->createNote($especialista->id, $validated);

        return back()->with('success', 'Nota guardada correctamente.');
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        $this->especialistaService->updateNote($especialista->id, $id, $validated);

        return back()->with('success', 'Nota actualizada correctamente.');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Request $request, int $id)
    {
        $especialista = $request->user()->especialista;
        $this->especialistaService->deleteNote($especialista->id, $id);

        return back()->with('success', 'Nota eliminada.');
    }
}
