<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('person_id')->nullable()->comment('员工ID');
            $table->char('name', 20)->nullable()->comment('主体名称 不一定是员工的姓名');
            $table->string('device_sn')->comment('设备SN');
            $table->string('face')->nullable()->comment('通行图片路径');
            $table->tinyInteger('mask')->nullable()->comment('口罩:-1未带 1带');
            $table->float('temperature', 3, 1)->nullable()->comment('测温度数');
            $table->tinyInteger('orientation')->default(1)->comment('1进2出');
            $table->dateTime('screen_time')->comment('设备屏幕上的时间');
            $table->json('other')->nullable()->comment('其它信息');
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
        Schema::dropIfExists('records');
    }
}
