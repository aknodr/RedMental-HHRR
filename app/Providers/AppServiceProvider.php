<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Workaround for a Laravel BladeCompiler bug where `$forElseCounter`
        // (instance state) is not reset between compileString calls. When a
        // request compiles several views, the counter can drift negative and
        // produce invalid PHP variable names like `$__empty_-1`, breaking
        // the exception renderer's markdown.blade.php with a parse error.
        //
        // We reset the counter to 0 before every compileString call.
        Blade::prepareStringsForCompilationUsing(function (string $value): string {
            $compiler = $this->app->make(BladeCompiler::class);
            $r = new \ReflectionProperty($compiler, 'forElseCounter');
            $r->setAccessible(true);
            $r->setValue($compiler, 0);
            return $value;
        });
    }
}
