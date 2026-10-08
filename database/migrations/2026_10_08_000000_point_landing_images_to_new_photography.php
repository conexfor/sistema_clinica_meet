<?php

use App\Models\LandingImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Asigna la nueva dirección fotográfica a cada espacio de la landing.
 * Los archivos subidos anteriormente no se borran: siguen en storage/app/public/landing_images.
 */
return new class extends Migration
{
    public function up(): void
    {
        $order = 1;
        foreach (LandingImage::SLOTS as $position => $slot) {
            DB::table('landing_images')->updateOrInsert(
                ['position' => $position],
                ['filename' => $slot['default'], 'alt_text' => $slot['alt'], 'sort_order' => $order++, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Sin reversión: las rutas anteriores no se conservan.
    }
};
