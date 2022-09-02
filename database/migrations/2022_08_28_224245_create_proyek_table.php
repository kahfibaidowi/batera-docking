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
        Schema::create("tbl_proyek", function (Blueprint $table) {
            $table->id("id_proyek");
            $table->unsignedBigInteger("id_user")->comment("pemilik kapal/owner");
            $table->text("vessel");
            $table->unsignedInteger("tahun");
            $table->text("foto");
            $table->text("currency");
            $table->text("prioritas")->default("hight");
            $table->text("negara");
            $table->text("deskripsi");
            $table->text("status")->default("draft");
            $table->text("tender_status")->default("pending");
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
        Schema::dropIfExists("tbl_proyek");
    }
};
