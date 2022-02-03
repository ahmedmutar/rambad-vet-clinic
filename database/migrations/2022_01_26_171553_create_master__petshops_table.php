<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPetshopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Master_petshops', function (Blueprint $table) {
          $table->id();
          $table->string('payment_number');
          $table->boolean('isDeleted')->nullable()->default(false);
          $table->integer('user_id');
          $table->integer('branch_id');
          $table->integer('user_update_id')->nullable();
          $table->string('deleted_by')->nullable();
          $table->timestamp('deleted_at', 0)->nullable();
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
        Schema::dropIfExists('Master_petshops');
    }
}
