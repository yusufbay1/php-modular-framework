<?php

use Core\Database\{Blueprint, Schema};

return new class {
    public function up(): void
    {
        Schema::create('home', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('home');
    }
};