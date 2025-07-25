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
        Schema::create('expenses', function (Blueprint $table) {
    $table->engine = 'InnoDB';
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
    $table->string('description');
    $table->decimal('amount', 10, 2);
    $table->date('expense_date');
    $table->string('category');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->text('notes')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense');
    }
};
