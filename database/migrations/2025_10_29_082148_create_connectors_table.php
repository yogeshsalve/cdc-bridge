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
       Schema::create('connectors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('db_type'); // postgres/mysql
    $table->string('host');
    $table->integer('port');
    $table->string('database');
    $table->string('username');
    $table->string('password');
    $table->string('status')->default('inactive');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connectors');
    }
};
