<?php

namespace CedricKassen\Helpdesk\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class HelpdeskRoutesProvider extends RouteServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['web', 'auth'])
                ->group(plugin_path('helpdesk', 'routes/web.php'));
        });
    }
}
