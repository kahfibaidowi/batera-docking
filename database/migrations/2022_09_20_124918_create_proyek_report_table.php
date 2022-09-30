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
        Schema::create("tbl_proyek_report", function (Blueprint $table) {
            $table->id("id_proyek_report");
            $table->unsignedBigInteger("id_proyek")->unique();
            $table->unsignedBigInteger("id_tender")->unique();
            $table->date("proyek_start")->nullable();
            $table->date("proyek_end")->nullable();
            $table->unsignedInteger("proyek_period")->default(0);
            $table->text("master_plan");
            $table->text("status");
            $table->text("state");
            $table->text("tipe_proyek");
            $table->text("prioritas");
            $table->text("partner");
            $table->text("deskripsi");
            $table->text("work_area")->default("[]")->comment("list pekerjaan/work area, data berbentuk json");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek")
                ->references("id_proyek")
                ->on("tbl_proyek")
                ->onDelete("cascade");

            $table->foreign("id_tender")
                ->references("id_tender")
                ->on("tbl_tender")
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
        Schema::dropIfExists("tbl_proyek_report");
    }
};
