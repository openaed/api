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
            $table->decimal('latitude', 12, 10);
            $table->decimal('longitude', 12, 10);
            $table->json('raw_osm')->nullable();;

            $table->foreignUuid('operator_id')->nullable()->constrained('operators');

            $table->string('access')->nullable();
            $table->boolean(column: 'indoor')->nullable();
            $table->boolean(column: 'locked')->nullable();
            $table->string(column: 'location')->nullable(); // Dutch version; other language versions are available in another table
            $table->string(column: 'manufacturer')->nullable();
            $table->string(column: 'model')->nullable();
            $table->string(column: 'opening_hours')->nullable();

            $table->string('image')->nullable();

            $table->timestamp('last_synced_at')->nullable();
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
