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
        Schema::create('tbl_users_shipowner', function (Blueprint $table) {
            $table->id("id_user_shipowner");
            $table->unsignedBigInteger("id_user")->unique()->comment("shipowner");
            $table->unsignedInteger("kapal_tersisa");
            $table->timestamps();

            $table->foreign("id_user")
                ->references("id_user")
                ->on("tbl_users")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_users_shipowner');
    }
};
