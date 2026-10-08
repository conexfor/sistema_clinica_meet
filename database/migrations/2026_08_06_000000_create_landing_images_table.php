<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_images', function (Blueprint $table) {
            $table->id();
            $table->string('position')->unique();
            $table->string('filename')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('landing_images')->insert([
            ['position' => 'gallery_1', 'filename' => 'images/650658521_1505733138225960_4746970566053627693_n.jpg', 'alt_text' => 'Sala de espera principal', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['position' => 'gallery_2', 'filename' => 'images/625971996_1471348794997728_8591781840000181479_n.jpg', 'alt_text' => 'Consultorio con detalle natural', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['position' => 'gallery_3', 'filename' => 'images/625958492_1471348741664400_4645495522416018553_n.jpg', 'alt_text' => 'Equipo clínico en formación', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['position' => 'gallery_4', 'filename' => 'images/626256792_1471348691664405_1024310863585606939_n.jpg', 'alt_text' => 'Ambiente luminoso y cálido', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_images');
    }
};
