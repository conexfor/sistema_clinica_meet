<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'disponibilidad_id' => 'required|exists:disponibilidades,id',
            'servicio_id' => 'nullable|exists:servicios,id',
            'paciente_id' => 'nullable|exists:usuarios,id',
            'notas' => 'nullable|string|max:500',
            'cantidad_sesiones' => 'nullable|integer',
            'tipo_consulta' => 'nullable|string',
            'duracion_minutos' => 'nullable|integer',
            'modalidad_id' => 'required|exists:modalidades,id',
            'reschedule_id' => 'nullable|exists:citas,id',
            'metodo_pago' => 'nullable|string',
            'tipo_comprobante' => 'nullable|string',
            'total_manual' => 'nullable|numeric',
        ];
    }
}
