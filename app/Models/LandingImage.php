<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class LandingImage extends Model
{
    use HasFactory;

    protected $table = 'landing_images';

    protected $fillable = [
        'filename',
        'alt_text',
        'position',
    ];

    /**
     * Espacios de imagen de la landing, en orden de aparición.
     * - group: sección del panel de administración.
     * - hint: orientación recomendada para la fotografía.
     * - default: fotografía incluida con el proyecto (public/images/landing).
     * - focus: encuadre (object-position) pensado para la fotografía incluida.
     */
    public const SLOTS = [
        'hero_image' => ['group' => 'Hero', 'label' => 'Interior luminoso', 'hint' => 'Horizontal · 2400 px', 'alt' => 'Salón luminoso visto a través de un arco, con sofá, mesas de madera y una ventana', 'default' => 'images/landing/hero.jpg', 'focus' => '50% 56%'],

        'service_1' => ['group' => 'Servicios', 'label' => 'Psicoterapia individual', 'hint' => 'Vertical 4:5', 'alt' => 'Rincón blanco con mesa redonda de madera, silla, lámpara de arco y plantas', 'default' => 'images/landing/servicio-individual.jpg', 'focus' => '50% 60%'],
        'service_2' => ['group' => 'Servicios', 'label' => 'Terapia de pareja', 'hint' => 'Vertical 4:5', 'alt' => 'Sala con sofá claro, sillón y un gran ventanal con cortinas de lino', 'default' => 'images/landing/servicio-pareja.jpg', 'focus' => '58% 50%'],
        'service_3' => ['group' => 'Servicios', 'label' => 'Procesos de transición', 'hint' => 'Vertical 4:5', 'alt' => 'Sillas de madera junto a una mesa redonda bajo la luz cálida de la tarde', 'default' => 'images/landing/servicio-transicion.jpg', 'focus' => '60% 45%'],
        'service_4' => ['group' => 'Servicios', 'label' => 'Consulta online', 'hint' => 'Vertical 4:5', 'alt' => 'Escalera blanca de líneas sencillas que sube hacia la luz', 'default' => 'images/landing/servicio-online.jpg', 'focus' => '60% 55%'],

        'about_image' => ['group' => 'Nosotros', 'label' => 'Sala de acogida', 'hint' => 'Vertical 3:4', 'alt' => 'Sofá blanco y mesa de madera bajo las sombras de un ventanal', 'default' => 'images/landing/nosotros.jpg', 'focus' => '45% 70%'],

        'space_1' => ['group' => 'El espacio', 'label' => 'Umbral · panorámica', 'hint' => 'Panorámica 21:9', 'alt' => 'Sala abierta con sofá amplio y un gran ventanal hacia el agua', 'default' => 'images/landing/espacio-umbral.jpg', 'focus' => '50% 62%'],
        'space_2' => ['group' => 'El espacio', 'label' => 'Materia · secundaria', 'hint' => 'Horizontal 4:3', 'alt' => 'Salón de techo alto con viga de madera, sofá claro y butacas', 'default' => 'images/landing/espacio-materia.jpg', 'focus' => '50% 78%'],
        'space_3' => ['group' => 'El espacio', 'label' => 'Intimidad · vertical', 'hint' => 'Vertical 3:4', 'alt' => 'Banco de madera con cojines de lino junto a una cortina translúcida', 'default' => 'images/landing/espacio-intimidad.jpg', 'focus' => '60% 65%'],
    ];

    public function getUrlAttribute(): ?string
    {
        if (! $this->filename) {
            return null;
        }

        if (str_starts_with($this->filename, 'http')) {
            return $this->filename;
        }

        if (str_starts_with($this->filename, 'storage/')) {
            return asset($this->filename);
        }

        if (str_starts_with($this->filename, 'landing_images/') || str_starts_with($this->filename, 'public/landing_images/')) {
            return asset('storage/' . ltrim($this->filename, '/'));
        }

        return asset($this->filename);
    }

    /**
     * Resuelve la imagen de un espacio: imagen subida → fotografía incluida.
     * El encuadre solo se aplica a la fotografía incluida, para la que fue pensado.
     *
     * @param  Collection<string, LandingImage>  $images  registros indexados por posición
     * @return array{url: string, alt: string, focus: string}
     */
    public static function resolve(Collection $images, string $position): array
    {
        $slot = self::SLOTS[$position];
        $record = $images->get($position);
        $filename = $record?->url ? $record->filename : $slot['default'];

        return [
            'url' => $record?->url ?: asset($slot['default']),
            'alt' => $record?->alt_text ?: $slot['alt'],
            'focus' => $filename === $slot['default'] ? $slot['focus'] : '50% 50%',
        ];
    }
}
