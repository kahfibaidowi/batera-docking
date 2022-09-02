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
            $table->unsignedBigInteger("id_proyek")->comment("proyek tender");
            $table->unsignedBigInteger("id_user")->comment("shipyard yang mengikuti tender");
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
            $table->double("diskon_umum_persen")->default(0);
            $table->double("diskon_tambahan");
            $table->text("status")->default("draft");
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
