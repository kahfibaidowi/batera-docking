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
        Schema::create('tbl_perusahaan', function (Blueprint $table) {
            $table->id("id_perusahaan");
            $table->text("nama_perusahaan");
            $table->text("merk_perusahaan");
            $table->text("alamat_perusahaan_1");
            $table->text("alamat_perusahaan_2");
            $table->text("telepon");
            $table->text("fax");
            $table->text("npwp");
            $table->text("email");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_perusahaan');
    }
};
