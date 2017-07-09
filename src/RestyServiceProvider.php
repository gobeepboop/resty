<?php

namespace Beep\Resty;

use Beep\Resty\Fractal\Serializer;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Manager as FractalManager;

class RestyServiceProvider extends ServiceProvider
{
    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register(): void
    {
        // Fractal
        $this->app->bind(
            FractalManager::class,
            function (): FractalManager {
                return (new FractalManager())->setSerializer(new Serializer)->parseIncludes(
                    $this->app->make(Request::class)->input('includes', '')
                );
            }
        );
    }
}
