<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('uploaded_files')) {
            return;
        }

        Schema::create('uploaded_files', function (Blueprint $table): void {
            $table->string('filename', 191)->collation('utf8mb4_bin')->primary();
            $table->string('mime_type', 100);
            $table->mediumBlob('content');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
