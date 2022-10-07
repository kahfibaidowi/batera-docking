<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("tbl_users", function(Blueprint $table){
            $table->id("id_user");
            $table->text("username")->unique();
            $table->text("nama_kapal");
            $table->text("title");
            $table->text("nama_lengkap");
            $table->text("jabatan");
            $table->text("no_hp");
            $table->text("departemen_id");
            $table->text("departemen");
            $table->text("email")->unique();
            $table->text("password");
            $table->text("avatar_url");
            $table->text("role");
            $table->text("status")->default("active");
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
        Schema::dropIfExists("tbl_users");
    }
}
