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
        Schema::table('book_enrollments', function (Blueprint $table) {
            $table->integer('current_page')->default(1)->after('book_id');
            $table->integer('total_pages')->nullable()->after('current_page');
            $table->timestamp('last_read_at')->nullable()->after('total_pages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_enrollments', function (Blueprint $table) {
            $table->dropColumn(['current_page', 'total_pages', 'last_read_at']);
        });
    }
};
