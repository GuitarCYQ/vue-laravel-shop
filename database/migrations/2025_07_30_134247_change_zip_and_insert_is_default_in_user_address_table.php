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
        Schema::table('user_addresses', function (Blueprint $table) {
            // 修改 zip 字段
            if (Schema::hasColumn('user_addresses', 'zip')) {
                $table->string('zip')->nullable()->change();
            }

            // 添加 is_default 字段（带严格检查）
            if (!Schema::hasColumn('user_addresses', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('contact_phone');
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_address', function (Blueprint $table) {
            // 如果修改了zip类型，回滚时需恢复原类型
            $table->unsignedInteger('zip')->change();
            $table->dropColumn('is_default'); // 再删字段
        });
    }
};
