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
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name','255');
            $table->string('email','255');
            $table->string('phone','25');
            $table->string('company','255');
            $table->integer('province_id');
            $table->integer('city_id');
            $table->string('logo','255');
            $table->string('address','255');
            $table->string('link','255');
            $table->text('content');
            $table->tinyInteger('status');
            $table->integer('updated_by');
            $table->integer('created_by');
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
        Schema::dropIfExists('publishers');
    }
};
