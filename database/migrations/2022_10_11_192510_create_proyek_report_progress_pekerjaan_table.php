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
        Schema::create('tbl_proyek_report_progress_pekerjaan', function (Blueprint $table) {
            $table->id("id_proyek_report_progress_pekerjaan");
            $table->unsignedBigInteger("id_proyek_report");
            $table->double("progress");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek_report")
                ->references("id_proyek_report")
                ->on("tbl_proyek_report")
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
        Schema::dropIfExists('tbl_proyek_report_progress_pekerjaan');
    }
};
