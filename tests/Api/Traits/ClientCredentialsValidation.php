<?php

namespace Tests\Api\Traits;

use App\User;
use Closure;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use Laravel\Passport\Passport;

trait ClientCredentialsValidation
{
    private function mockPassportClientValidation(): void
    {
        Passport::actingAs($this->create(User::class), ['*']);
        $this->afterApplicationCreated(function () {
            app()->bind(CheckClientCredentials::class, function () {
                return new class
                {
                    public function handle($request, Closure $next)
                    {
                        return $next($request);
                    }
                };
            });
        });
    }

}