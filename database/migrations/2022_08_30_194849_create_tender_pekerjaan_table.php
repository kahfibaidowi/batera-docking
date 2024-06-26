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
        Schema::create("tbl_tender_pekerjaan", function (Blueprint $table) {
            $table->id("id_tender_pekerjaan");
            $table->unsignedBigInteger("id_tender");
            $table->unsignedBigInteger("id_proyek_pekerjaan")->nullable();
            $table->text("pekerjaan");
            $table->text("satuan");
            $table->double("qty");
            $table->double("harga_satuan");
            $table->text("kategori_1");
            $table->text("kategori_2");
            $table->text("kategori_3");
            $table->text("kategori_4");
            $table->date("deadline");
            $table->timestamps();


            //fk
            $table->foreign("id_tender")
                ->references("id_tender")
                ->on("tbl_tender")
                ->onDelete("cascade");
            
            $table->foreign("id_proyek_pekerjaan")
                ->references("id_proyek_pekerjaan")
                ->on("tbl_proyek_pekerjaan")
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
        Schema::dropIfExists("tbl_tender_pekerjaan");
    }
};
