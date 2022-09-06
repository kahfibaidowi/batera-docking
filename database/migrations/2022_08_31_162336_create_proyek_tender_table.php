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
        Schema::create("tbl_proyek_tender", function (Blueprint $table) {
            $table->id("id_proyek_tender");
            $table->unsignedBigInteger("id_proyek")->unique()->comment("proyek tender");
            $table->unsignedBigInteger("id_user")->comment("shipyard yang menang tender");
            $table->date("rencana_off_hire_start");
            $table->date("rencana_off_hire_end");
            $table->unsignedInteger("rencana_off_hire_period");
            $table->unsignedInteger("rencana_off_hire_deviasi");
            $table->double("rencana_off_hire_rate_per_day");
            $table->double("rencana_off_hire_bunker_per_day");
            $table->date("rencana_repair_start");
            $table->date("rencana_repair_end");
            $table->unsignedInteger("rencana_repair_period");
            $table->date("rencana_repair_in_dock_start");
            $table->date("rencana_repair_in_dock_end");
            $table->unsignedInteger("rencana_repair_in_dock_period");
            $table->unsignedInteger("rencana_repair_additional_day");
            $table->double("rencana_diskon_umum_persen")->default(0);
            $table->double("rencana_diskon_tambahan");
            // $table->date("realisasi_off_hire_start")->nullable();
            // $table->date("realisasi_off_hire_end")->nullable();
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
        Schema::dropIfExists("tbl_proyek_tender");
    }
};
