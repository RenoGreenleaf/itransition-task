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
        Schema::create('product_data', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 50);
            $table->string('description', length: 255);
            $table->string('code', length: 10)->unique();
            $table->dateTime('added')->nullable();
            $table->dateTime('discontinued')->nullable();
            $table->timestamp('timestamp')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
