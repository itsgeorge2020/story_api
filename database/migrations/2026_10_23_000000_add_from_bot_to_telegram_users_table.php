<?php

use App\Enums\BotEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->string('from_bot')->nullable()->after('is_bot');
        });

        DB::table('telegram_users')->update(['from_bot' => BotEnum::STORY->value]);

        Schema::table('telegram_users', function (Blueprint $table) {
            $table->string('from_bot')->nullable(false)->change();
            $table->dropUnique(['telegram_id']);
            $table->unique(['telegram_id', 'from_bot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropUnique(['telegram_id', 'from_bot']);
            $table->unique('telegram_id');
            $table->dropColumn('from_bot');
        });
    }
};
