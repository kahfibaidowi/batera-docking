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
        Schema::create("tbl_proyek_tender_pekerjaan_realisasi", function (Blueprint $table) {
            $table->id("id_proyek_tender_pekerjaan_realisasi");
            $table->unsignedBigInteger("id_proyek_tender_pekerjaan");
            $table->unsignedBigInteger("id_user")->comment("pengupdate activity progress");
            $table->unsignedBigInteger("id_user_konfirmasi")->nullable()->comment("konfirmasi dari owner");
            $table->dateTime("tgl_realisasi");
            $table->double("qty")->comment("kuantitas pekerjaan per hari");
            $table->double("harga_satuan");
            $table->text("status_pekerjaan")->comment("status pekerjaan");
            $table->text("status")->default("pending")->comment("status progress diupdate owner");
            $table->text("komentar_rejected");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek_tender_pekerjaan")
                ->references("id_proyek_tender_pekerjaan")
                ->on("tbl_proyek_tender_pekerjaan")
                ->onDelete("cascade");
                
            $table->foreign("id_user")
                ->references("id_user")
                ->on("tbl_users")
                ->onDelete("cascade");

            $table->foreign("id_user_konfirmasi")
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
        Schema::dropIfExists("tbl_proyek_tender_pekerjaan_realisasi");
    }
};
