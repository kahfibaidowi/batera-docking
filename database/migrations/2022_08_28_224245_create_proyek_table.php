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
            $table->unsignedBigInteger("id_user")->comment("responsible");
            $table->unsignedBigInteger("id_kapal")->comment("referensi kapal");
            $table->unsignedInteger("tahun");
            $table->text("mata_uang");
            $table->date("off_hire_start");
            $table->date("off_hire_end");
            $table->unsignedInteger("off_hire_period");
            $table->unsignedInteger("off_hire_deviasi");
            $table->double("off_hire_rate_per_day");
            $table->double("off_hire_bunker_per_day");
            $table->date("repair_start");
            $table->date("repair_end");
            $table->unsignedInteger("repair_period");
            $table->date("repair_in_dock_start");
            $table->date("repair_in_dock_end");
            $table->unsignedInteger("repair_in_dock_period");
            $table->unsignedInteger("repair_additional_day");
            $table->text("work_area")->nullable()->comment("list pekerjaan/work area, data berbentuk json");
            $table->text("status")->comment("status pembuatan proyek [draft/published]");
            $table->timestamps();

            //fk
            $table->foreign("id_kapal")
                ->references("id_kapal")
                ->on("tbl_kapal")
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
        Schema::dropIfExists("tbl_proyek");
    }
};
