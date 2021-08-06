<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('IS_TASK', 1)){
            Schema::create('issued_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('device_sn')->comment('设备SN');
                $table->integer('count')->nullable()->comment('下发总数');
                $table->json('info')->comment('任务载荷数据');
                $table->tinyInteger('status')->default(0)->comment('0等待下发 1已下发 ');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issued_tasks');
    }
}
