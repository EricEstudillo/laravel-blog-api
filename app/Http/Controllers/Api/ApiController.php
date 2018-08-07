<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Helpers\AbstractResponseBuilder;
use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    /** @var AbstractResponseBuilder */
    protected $responseBuilder;
    protected const VALIDATION_RULES = [];

    public function __construct()
    {
        $this->responseBuilder = $this->instantiateResponseBuilder();
    }

    abstract protected function instantiateResponseBuilder();

    public static function fieldsToValidate()
    {
        return array_keys(static::VALIDATION_RULES);
    }
}