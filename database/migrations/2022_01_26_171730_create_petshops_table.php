<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetshopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('petshops', function (Blueprint $table) {
          $table->id();
          $table->integer('list_of_item_id');
          $table->integer('master_petshop_id');
          $table->integer('total_item');
          $table->boolean('isDeleted')->nullable()->default(false);
          $table->integer('user_id');
          $table->integer('user_update_id')->nullable();
          $table->string('deleted_by')->nullable();
          $table->timestamp('deleted_at',0)->nullable();
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
        Schema::dropIfExists('petshops');
    }
}
