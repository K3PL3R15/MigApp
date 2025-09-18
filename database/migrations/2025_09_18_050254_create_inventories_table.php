<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            // Estructura de la tabla
            $table->id('id_inventory');
            $table->string('name');
            $table->enum('type',['sale_product', 'raw_material']);
            $table->unsignedBigInteger('id_branch');
            // Foranea de Sucursal
            $table->foreign('id_branch')
                ->references('id_branch')
                ->on('branches')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            // Timestamps
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::enableForeignKeyConstraints();
    }
};
