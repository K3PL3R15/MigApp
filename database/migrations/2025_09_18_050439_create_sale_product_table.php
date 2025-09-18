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
        Schema::create('sale_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sale');
            $table->unsignedBigInteger('id_product');
            $table->integer('quantity'); // Cantidad de este producto vendido
            $table->decimal('unit_price', 10, 2); // Precio unitario al momento de la venta
            $table->decimal('subtotal', 10, 2); // quantity * unit_price
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('id_sale')
                ->references('id_sale')
                ->on('sales')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            $table->foreign('id_product')
                ->references('id_product')
                ->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');
                
            // Índice único para evitar duplicados
            $table->unique(['id_sale', 'id_product']);
        });
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_product');
        Schema::enableForeignKeyConstraints();
    }
};
