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
        Schema::table('products', function (Blueprint $table) {
            // 热门推荐(布尔值：0/1)
            $table->boolean('is_hot')->default(0)->comment('是否热门推荐');

            // 显示则扣：需要时间范围和折扣值
            $table->timestamp('discount_start_time')->nullable()->comment('折扣开始时间');
            $table->timestamp('discount_end_time')->nullable()->comment('折扣结束时间');
            $table->decimal('discount', 5, 2)->nullable()->comment('折扣比例 （如0.85表示85折）');

            // 新品上市：布尔值（0/1）
            $table->boolean('is_new')->default(0)->comment('是否新品上市');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_hot', 'discount_start_time', 'discount_end_time', 'discount', 'is_new']);
        });
    }
};
