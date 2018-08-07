<?php

namespace Tests;

use App\User;
use function factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $nonExistentId = 999;
    protected $endPoint;

    public function signIn($user = null)
    {
        $user = $user ?? factory(User::class)->create();
        $this->actingAs($user);
    }

    public function create(string $className, array $attributes = [], $times = null)
    {
        return factory($className, $times)->create($attributes);
    }

    protected function getSpecificEndPoint($id): string
    {
        return "{$this->endPoint}/{$id}";
    }
}
