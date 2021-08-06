<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentPersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_persons', function (Blueprint $table) {
            $table->string('device_sn')->comment('设备SN');
            $table->bigInteger('person_id')->comment('人员ID');
            $table->tinyInteger('status')->default(0)->comment('0正在下发1下发完成');
            $table->unique(['device_sn', 'person_id']); //联合唯一索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_persons');
    }
}
