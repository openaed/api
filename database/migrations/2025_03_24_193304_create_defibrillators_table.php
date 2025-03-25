<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('defibrillators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('osm_id')->unique()->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('raw_osm');

            $table->foreignUuid('operator_id')->nullable()->constrained('operators');

            $table->string('access');
            $table->boolean(column: 'indoor');
            $table->boolean(column: 'locked');
            $table->string(column: 'location'); // Dutch version; other language versions are available in another table
            $table->string(column: 'manufacturer');
            $table->string(column: 'model');
            $table->string(column: 'opening_hours');

            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defibrillators');
    }
};