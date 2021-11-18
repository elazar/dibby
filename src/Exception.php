<?php

namespace Elazar\Dibby;

use Throwable;

class Exception extends \Exception
{
    public const CODE_DATABASE_MISSING_SQLITE_PATH = 1;
    public const CODE_DATABASE_MISSING_DRIVER      = 2;
    public const CODE_DATABASE_CONNECTION_FAILED   = 3;
    public const CODE_DATABASE_DRIVER_UNAVAILABLE  = 4;
    public const CODE_DATABASE_UNKNOWN_ERROR       = 5;
    public const CODE_JWT_INVALID                  = 6;
    public const CODE_USER_NOT_FOUND               = 7;

    public static function databaseMissingSqlitePath(): self
    {
        return new self(
            'Database host / path setting is required when using pdo_sqlite driver',
            self::CODE_DATABASE_MISSING_SQLITE_PATH,
        );
    }

    public static function databaseMissingDriver(Throwable $previous): self
    {
        return new self(
            'Database driver setting is required',
            self::CODE_DATABASE_MISSING_DRIVER,
            $previous,
        );
    }

    public static function databaseConnectionFailed(Throwable $previous): self
    {
        return new self(
            'Failed to connect to database server',
            self::CODE_DATABASE_CONNECTION_FAILED,
            $previous,
        );
    }

    public static function databaseDriverUnavailable(string $driver, Throwable $previous): self
    {
        return new self(
            'Database driver is unavailable: ' . $driver,
            self::CODE_DATABASE_DRIVER_UNAVAILABLE,
            $previous,
        );
    }

    public static function databaseUnknownError(Throwable $previous): self
    {
        return new self(
            'Unknown database error',
            self::CODE_DATABASE_UNKNOWN_ERROR,
            $previous,
        );
    }

    public static function jwtInvalid(string $error): self
    {
        return new self(
            'JWT token is invalid: ' . $error,
            self::CODE_JWT_INVALID,
        );
    }

    public static function userNotFound(string $userIdOrEmail): self
    {
        return new self(
            'User not found: ' . $userIdOrEmail,
            self::CODE_USER_NOT_FOUND,
        );
    }
}
