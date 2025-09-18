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
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');
            $table->unsignedBigInteger('id_inventory'); // 🔑 FK hacia inventario
            $table->string('name');
            $table->date('lote');
            $table->integer('stock');
            $table->integer('expiration_days');
            $table->integer('min_stock');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // Relación con inventory
            $table->foreign('id_inventory')
                ->references('id_inventory')
                ->on('inventories')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::enableForeignKeyConstraints();
    }
};
