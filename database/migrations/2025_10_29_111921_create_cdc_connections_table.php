<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('cdc_connections', function (Blueprint $table) {
    $table->id();
    $table->string('db_type')->default('mysql');
    $table->string('host');
    $table->string('port')->default('3306');
    $table->string('username');
    $table->string('password');
    $table->string('database_name');
    $table->string('table_name');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdc_connections');
    }
};
