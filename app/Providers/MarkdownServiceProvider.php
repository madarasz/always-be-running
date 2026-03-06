<?php

namespace App\Providers;

use App\Support\Markdown\MarkdownRenderer;
use Illuminate\Support\ServiceProvider;

class MarkdownServiceProvider extends ServiceProvider
{
    /**
     * Register markdown rendering services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('markdown', function () {
            return new MarkdownRenderer();
        });
    }
}
