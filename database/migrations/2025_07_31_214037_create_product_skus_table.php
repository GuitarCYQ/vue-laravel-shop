<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_skus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('SKU 标题');
            $table->string('description')->nullable()->comment('SKU描述');
            $table->decimal('price', 10, 2)->comment('SKU 价格');
            $table->unsignedInteger('stock')->default(0)->comment('库存数量');
            $table->unsignedBigInteger('product_id')->comment('所属商品ID');
            $table->timestamps();

            // 关联商品表（商品删除时，SKU也删除）
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_skus');
    }
};
