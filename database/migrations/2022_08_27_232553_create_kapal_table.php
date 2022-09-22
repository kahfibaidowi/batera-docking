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
        Schema::create("tbl_kapal", function (Blueprint $table) {
            $table->id("id_kapal");
            $table->unsignedBigInteger("id_user")->comment("pemilik kapal");
            $table->unsignedBigInteger("id_perusahaan")->comment("perusahaan");
            $table->text("nama_kapal");
            $table->text("foto");
            $table->timestamps();

            //fk
            $table->foreign("id_user")
                ->references("id_user")
                ->on("tbl_users")
                ->onDelete("cascade");

            $table->foreign("id_perusahaan")
                ->references("id_perusahaan")
                ->on("tbl_perusahaan")
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
        Schema::dropIfExists("tbl_kapal");
    }
};
