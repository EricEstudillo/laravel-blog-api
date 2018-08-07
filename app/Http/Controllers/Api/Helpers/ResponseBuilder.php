<?php

namespace App\Http\Controllers\Api\Helpers;

use Illuminate\Http\JsonResponse;

interface ResponseBuilder
{
    public function createStoreResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse;

    public function createShowResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse;

    public function createListResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse;

    public function createUpdateResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse;

    public function createDestroyResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse;
}