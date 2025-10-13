<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->text('image')->nullable();
            $table->string('name_kh')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_cn')->nullable();
            $table->string('category_id')->nullable();
            $table->integer('qty')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->text('remark')->nullable();
            $table->boolean('is_show')->default(true);
            $table->timestamps();
            

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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