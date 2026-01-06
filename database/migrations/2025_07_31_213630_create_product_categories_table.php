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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigIncrements('id'); // 主键（bigint类型）
            $table->string('name', 100)->comment('分类名称');
            $table->text('description')->nullable()->comment('分类描述');
            $table->string('icon')->nullable()->comment('分类图标URL');
            $table->unsignedBigInteger('parent_id')->nullable()->default(0)->comment('父分类ID（多级分类）');
            $table->unsignedInteger('sort')->default(0)->comment('排序权重（数字越小越靠前）');
            $table->timestamps();

            // 自关联外键（用于多级分类）
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};
