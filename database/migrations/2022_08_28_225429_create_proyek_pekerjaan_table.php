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
        Schema::create("tbl_proyek_pekerjaan", function (Blueprint $table) {
            $table->id("id_proyek_pekerjaan");
            $table->unsignedBigInteger("id_proyek");
            $table->text("pekerjaan");
            $table->text("satuan");
            $table->double("qty");
            $table->text("kategori_1");
            $table->text("kategori_2");
            $table->text("kategori_3");
            $table->text("kategori_4");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek")
                ->references("id_proyek")
                ->on("tbl_proyek")
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
        Schema::dropIfExists("tbl_proyek_pekerjaan");
    }
};
