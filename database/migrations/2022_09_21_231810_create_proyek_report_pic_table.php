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
        Schema::create("tbl_proyek_report_pic", function (Blueprint $table) {
            $table->id("id_proyek_report_pic");
            $table->unsignedBigInteger("id_proyek_report");
            $table->unsignedBigInteger("id_user");
            $table->timestamps();

            //fk
            $table->foreign("id_proyek_report")
                ->references("id_proyek_report")
                ->on("tbl_proyek_report")
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
        Schema::dropIfExists("tbl_proyek_report_pic");
    }
};
