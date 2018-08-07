<?php


class AllowedActionsExceptions extends InvalidArgumentException
{
    public function __construct(string $action)
    {
        $message = sprintf("Action %s not allowed", $action);
        parent::__construct($message);
    }

    public static function throw(string $action)
    {
        throw new AllowedActionsExceptions($action);
    }
}