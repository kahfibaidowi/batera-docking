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
            $table->text("nama_kapal");
            $table->text("foto");
            $table->text("nama_perusahaan");
            $table->text("merk_perusahaan");
            $table->text("alamat_perusahaan_1");
            $table->text("alamat_perusahaan_2");
            $table->text("telepon");
            $table->text("faximile");
            $table->text("npwp");
            $table->text("email");
            $table->timestamps();

            //fk
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
        Schema::dropIfExists("tbl_kapal");
    }
};
