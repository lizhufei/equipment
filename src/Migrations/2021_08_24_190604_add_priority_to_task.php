<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityToTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('issued_tasks', function (Blueprint $table) {
            $table->tinyInteger('priority')
                ->after('created_at')
                ->default(0)
                ->comment('0普通1高优先;总共两级');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issued_tasks', function (Blueprint $table) {
            //
        });
    }
}
