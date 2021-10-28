<?php


namespace SibertSchurmans\LaravelMangoPay;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use MangoPay\MangoPayApi;

class MangoPayServiceProvider extends ServiceProvider
{
    /**
     * The Mangopay URLs used by the API
     */
    public const BASE_URL_SANDBOX = 'https://api.sandbox.mangopay.com';
    public const BASE_URL_PRODUCTION = 'https://api.mangopay.com';

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mangopay.php' => config_path('mangopay.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mangopay.php', 'mangopay'
        );

        $this->app->singleton(MangoPayApi::class, function ($app) {
            $config = $app['config']['mangopay'];

            if (!$clientId = Arr::get($config, 'key')) {
                throw new InvalidArgumentException('Mangopay key not configured');
            }

            if (!$clientPassword = Arr::get($config, 'secret')) {
                throw new InvalidArgumentException('Mangopay secret not configured');
            }

            if (!$env = Arr::get($config, 'env')) {
                throw new InvalidArgumentException('Mangopay environment not configured');
            }

            foreach (Arr::get($config, 'directories') as $env => $directory) {
                File::isDirectory($directory) or File::makeDirectory($directory, 0777, true, true);
            }

            $mangoPayApi = new MangoPayApi();
            $mangoPayApi->Config->ClientId = $clientId;
            $mangoPayApi->Config->ClientPassword = $clientPassword;
            $mangoPayApi->Config->BaseUrl = $this->getURL(Arr::get($config, 'env'));
            $mangoPayApi->Config->TemporaryFolder = Arr::get($config, 'directories')[Arr::get($config, 'env')];

            return $mangoPayApi;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [MangoPayApi::class];
    }

    /**
     * Return the appropriate API URL based on the environment.
     *
     * @param $environment
     * @return string
     */
    public function getURL($environment)
    {
        try {
            return constant('self::BASE_URL_' . strtoupper($environment));
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Mangopay environment should be one of "sandbox" or "production"');
        }
    }
}
