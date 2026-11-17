<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Storage\DatabaseConversationStore;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DatabaseConversationStore::class, fn ($app) => $app->make(ConversationStore::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
