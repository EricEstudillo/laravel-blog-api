<?php

namespace App\Http\Controllers\Api\Helpers;

use AllowedActionsExceptions;

class AllowedActions
{
    public const INDEX = 'index';
    public const SHOW = 'show';
    public const STORE = 'store';
    public const UPDATE = 'update';
    public const DESTROY = 'destroy';
    public const ACTIONS = [
        self::INDEX,
        self::SHOW,
        self::STORE,
        self::UPDATE,
        self::DESTROY,
    ];

    public static function guardAgainstInvalidAction(string $action)
    {
        if (!in_array($action, self::ACTIONS)) {
            AllowedActionsExceptions::throw($action);
        }
    }
}