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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('商品标题');
            $table->text('description')->comment('商品描述');
            $table->string('image')->comment('商品主图URL');
            $table->json('images')->nullable()->comment('商品轮播图）（json格式）');
            $table->boolean('on_sale')->default(true)->comment('是否在售');
            $table->float('rating')->default(5)->comment('评分 （0-5）');
            $table->unsignedInteger('sold_count')->default(0)->comment('销量');
            $table->unsignedInteger('review_count')->default(0)->comment('评论数');
            $table->decimal('price', 10, 2)->comment('商品价格');
            $table->unsignedBigInteger('category_id')->comment('所属分类id');
            $table->tinyInteger('status')->default(1)->comment('状态：1.正常 2.下架 3.预售');
            $table->string('slug')->unique()->nullable()->comment('SEO友好的URL标识');
            $table->timestamps();
            $table->softDeletes(); //软删除

            // 关联分类表
            $table->foreign('category_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('restrict'); //禁止删除有商品的分类
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
