<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draws', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->unsignedBigInteger('submitter'); 
            $table->integer('max_entries')->default(0);
            $table->integer('max_entries_per_player')->default(0);
            $table->integer('price')->default(0);
            $table->integer('winners')->default(1);
            $table->boolean('pined')->default(false);
            $table->boolean('is_open')->default(true);
            $table->boolean('automatic_draw')->default(true);
            $table->boolean('closed')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('draw_entries', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('draw_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('draw_id')->references('id')->on('draws')->cascadeOnDelete();
        });

        Schema::create('draw_winners', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('draw_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('draw_id')->references('id')->on('draws')->cascadeOnDelete();
        });

        Schema::create('draw_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('money')->default(0);
            $table->boolean('need_online')->default(false);
            $table->json('commands')->nullable();
            $table->timestamps();
        });

        Schema::create('draw_rewards_servers', function (Blueprint $table) {
            $table->unsignedInteger('server_id');
            $table->unsignedBigInteger('reward_id');

            $table->foreign('reward_id')->references('id')->on('draw_rewards')->cascadeOnDelete();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();

            $table->unique(['reward_id', 'server_id']);
        });

        Schema::create('draw_rewards_link', function (Blueprint $table) {
            $table->unsignedBigInteger('draw_id');
            $table->unsignedBigInteger('reward_id');

            $table->foreign('draw_id')->references('id')->on('draws')->cascadeOnDelete();
            $table->foreign('reward_id')->references('id')->on('draw_rewards')->cascadeOnDelete();

            $table->unique(['reward_id', 'draw_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draws');
        Schema::dropIfExists('draw_entries');
        Schema::dropIfExists('draw_winners');
        Schema::dropIfExists('draw_rewards');
        Schema::dropIfExists('draw_rewards_servers');
        Schema::dropIfExists('draw_rewards_link');
    }
};
