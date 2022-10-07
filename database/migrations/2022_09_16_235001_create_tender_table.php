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
        Schema::create("tbl_tender", function (Blueprint $table) {
            $table->id("id_tender");
            $table->unsignedBigInteger("id_proyek");
            $table->unsignedBigInteger("id_user")->comment("shipyard/galangan(responsible)");
            $table->text("dokumen_kontrak");
            $table->text("no_kontrak");
            $table->text("komentar");
            $table->text("nama_galangan");
            $table->text("lokasi_galangan");
            $table->double("yard_total_quote");
            $table->double("general_diskon_persen");
            $table->double("additional_diskon");
            $table->double("sum_internal_adjusment");
            $table->text("work_area")->nullable()->comment("list pekerjaan/work area, data berbentuk json");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek")
                ->references("id_proyek")
                ->on("tbl_proyek")
                ->onDelete("cascade");

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
        Schema::dropIfExists("tbl_tender");
    }
};
