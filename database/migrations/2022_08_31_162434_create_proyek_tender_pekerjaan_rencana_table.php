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
        Schema::create("tbl_proyek_tender_pekerjaan_rencana", function (Blueprint $table) {
            $table->id("id_proyek_tender_pekerjaan_rencana");
            $table->unsignedBigInteger("id_proyek_tender_pekerjaan");
            $table->double("qty")->comment("kuantitas pekerjaan per hari");
            $table->date("tgl_rencana");
            $table->text("keterangan");
            $table->timestamps();

            
            //fk
            $table->foreign("id_proyek_tender_pekerjaan")
                ->references("id_proyek_tender_pekerjaan")
                ->on("tbl_proyek_tender_pekerjaan")
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
        Schema::dropIfExists("tbl_proyek_tender_pekerjaan_rencana");
    }
};
