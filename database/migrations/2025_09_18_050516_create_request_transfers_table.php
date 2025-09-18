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
        Schema::create('request_transfers', function (Blueprint $table) {
            // Estructura de la tabla
            $table->id('id_request');
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_origin_branch');
            $table->unsignedBigInteger('id_destiny_branch');
            $table->integer('quantity_products'); // Corregido: 'quantity' en lugar de 'cuantity'
            $table->enum('state', ['pending', 'approved', 'rejected', 'completed']); // Mejorado: enum en lugar de string
            $table->dateTime('date_request');
            $table->timestamps();
            
            // Foranea Producto
            $table->foreign('id_product')
                ->references('id_product')
                ->on('products')
                ->onDelete('restrict');
                
            // Foranea Sucursal Origen y Destino
            $table->foreign('id_origin_branch')
                ->references('id_branch')
                ->on('branches')
                ->onDelete('restrict');

            $table->foreign('id_destiny_branch')
                ->references('id_branch')
                ->on('branches')
                ->onDelete('restrict');
        });
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_transfers');
        Schema::enableForeignKeyConstraints();
    }
};
