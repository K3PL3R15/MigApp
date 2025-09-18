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
        Schema::create('sales', function (Blueprint $table) {
            // Estructura de la tabla
            $table->id('id_sale');
            $table->dateTime('date');
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_branch');
            $table->text('justify')->nullable();
            // Foranea Usuario
            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            // Foranea Sucursal
            $table->foreign('id_branch')
                ->references('id_branch')
                ->on('branches')
                ->onDelete('restrict')
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
        Schema::dropIfExists('sales');
        Schema::enableForeignKeyConstraints();
    }
};
