<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add latitude / longitude to patients and clinics so the route planner can
 * compute distance + plot Leaflet markers without a live geocoder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $t) {
            $t->decimal('latitude',  10, 7)->nullable()->after('zip');
            $t->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
        Schema::table('clinics', function (Blueprint $t) {
            $t->decimal('latitude',  10, 7)->nullable()->after('zip');
            $t->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $t) {
            $t->dropColumn(['latitude', 'longitude']);
        });
        Schema::table('clinics', function (Blueprint $t) {
            $t->dropColumn(['latitude', 'longitude']);
        });
    }
};
