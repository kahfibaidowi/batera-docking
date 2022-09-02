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
        Schema::create("tbl_proyek_biaya", function (Blueprint $table) {
            $table->id("id_proyek_biaya");
            $table->unsignedBigInteger("id_proyek")->unique()->comment("dari proyek 1 to 1");
            $table->date("off_hire_start");
            $table->date("off_hire_end");
            $table->unsignedInteger("off_hire_period");
            $table->unsignedInteger("off_hire_deviasi");
            $table->double("off_hire_rate_per_day");
            $table->double("off_hire_bunker_per_day");
            $table->double("list_pekerjaan")->comment("perkiraan biaya untuk list pekerjaannya");
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
        Schema::dropIfExists("tbl_proyek_biaya");
    }
};
