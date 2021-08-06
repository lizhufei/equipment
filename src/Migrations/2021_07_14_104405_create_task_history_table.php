<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('person_id')->comment('人员ID');
            $table->string('device_sn')->comment('设备SN');
            $table->integer('code')->nullable()->comment('反馈代码');
            $table->string('msg')->default('')->comment('反馈信息');
            $table->string('contents')->comment('历史消息');
            $table->char('operate', 10)->comment('动作类型');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_history');
    }
}
