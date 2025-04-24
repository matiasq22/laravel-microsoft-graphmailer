<?php

namespace TuEmpresa\GraphMail;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use TuEmpresa\GraphMail\Transport\GraphTransport;

class GraphMailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/graphmail.php', 'graphmail');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/graphmail.php' => config_path('graphmail.php'),
        ]);

        app(MailManager::class)->extend('graph', function ($config) {
            return new GraphTransport(app(GraphMailService::class));
        });
    }
}