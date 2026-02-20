<?php

namespace App\Providers;

use App\Contracts\SheetServiceInterface;
use App\Services\GoogleSheetsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SheetServiceInterface::class, GoogleSheetsService::class);
    }

    public function boot(): void
    {
        //
    }
}
