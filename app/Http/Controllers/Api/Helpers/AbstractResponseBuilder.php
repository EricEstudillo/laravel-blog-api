<?php

namespace App\Http\Controllers\Api\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use function sprintf;

abstract class AbstractResponseBuilder implements ResponseBuilder
{
    protected const MODEL = '';
    private const OK_SHOW = 'Show %s/s successfully';
    private const OK_ACTION_SUCCESS = '%s %sd correctly';
    private const KO_NOT_FOUND = '%s not found';
    private const KO_ACTION_NO_SUCCESS = '%s cannot be %sd';
    private const KO_EXISTS = '%s already exists.';

    public function createStoreResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        return $this->create(AllowedActions::STORE, $statusCode, $errors, $data);
    }

    public function createShowResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        return $this->create(AllowedActions::SHOW, $statusCode, $errors, $data);
    }

    public function createListResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        return $this->create(AllowedActions::SHOW, $statusCode, $errors, $data);
    }

    public function createUpdateResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        return $this->create(AllowedActions::UPDATE, $statusCode, $errors, $data);
    }

    public function createDestroyResponse(int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        return $this->create(AllowedActions::DESTROY, $statusCode, $errors, $data);
    }

    protected function create(string $action, int $statusCode, array $errors = [], array $data = []): JsonResponse
    {
        AllowedActions::guardAgainstInvalidAction($action);

        return response()->json(
            [
                'message' => $this->message($statusCode, $action),
                'errors' => $errors,
                'data' => $data
            ],
            $statusCode
        );
    }

    public function message(int $statusCode, string $action = ''): string
    {
        $mapMessage = [
            Response::HTTP_OK => self::OK_SHOW,
            Response::HTTP_CREATED => self::OK_ACTION_SUCCESS,
            Response::HTTP_ACCEPTED => self::OK_ACTION_SUCCESS,
            Response::HTTP_NOT_FOUND => self::KO_NOT_FOUND,
            Response::HTTP_BAD_REQUEST => self::KO_ACTION_NO_SUCCESS,
            Response::HTTP_CONFLICT => self::KO_EXISTS,
        ];

        return sprintf($mapMessage[$statusCode] ?? '', static::MODEL, $action);
    }
}