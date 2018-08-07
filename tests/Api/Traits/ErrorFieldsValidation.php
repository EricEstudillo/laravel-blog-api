<?php

namespace Tests\Api\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait ErrorFieldsValidation
{
    protected function assertShowErrorFields(string $className, TestResponse $response): void
    {
        $content = json_decode($response->getContent());
        foreach ($className::fieldsToValidate() as $field) {
            $this->assertTrue(isset($content->errors->{$field}));
        }
    }
}