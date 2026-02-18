<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    /** @var list<callable> */
    private array $errorHandlerSnapshot = [];

    /** @var list<callable> */
    private array $exceptionHandlerSnapshot = [];

    /**
     * Laravel's HandleExceptions::flushHandlersState() strips ALL PHP
     * error/exception handlers during tearDown — including PHPUnit's own —
     * which triggers unconditional risky-test detection in PHPUnit 12.
     * We snapshot the handler stacks before Laravel touches them and restore
     * them after tearDown so PHPUnit sees no change.
     *
     * @see https://github.com/laravel/framework/issues/50951
     */
    protected function setUp(): void
    {
        $this->errorHandlerSnapshot = self::drainHandlerStack('error');
        $this->exceptionHandlerSnapshot = self::drainHandlerStack('exception');

        parent::setUp();

        // Disable CSRF verification for all tests since we are testing
        // JSON API behavior and CSRF is a transport-layer concern
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::drainHandlerStack('error');
        self::drainHandlerStack('exception');

        foreach ($this->errorHandlerSnapshot as $h) {
            set_error_handler($h);
        }
        foreach ($this->exceptionHandlerSnapshot as $h) {
            set_exception_handler($h);
        }
    }

    /**
     * Pop every handler off the given stack, then push them all back.
     *
     * @return list<callable> Handlers in bottom-to-top order.
     */
    private static function drainHandlerStack(string $type): array
    {
        $set = $type === 'error'
            ? static fn() => set_error_handler(static fn() => false)
            : static fn() => set_exception_handler(static fn() => null);
        $restore = $type === 'error'
            ? static fn() => restore_error_handler()
            : static fn() => restore_exception_handler();

        $handlers = [];

        while (true) {
            $h = $set();
            $restore();

            if ($h === null) {
                break;
            }

            $handlers[] = $h;
            $restore();
        }

        $handlers = array_reverse($handlers);

        foreach ($handlers as $h) {
            $type === 'error' ? set_error_handler($h) : set_exception_handler($h);
        }

        return $handlers;
    }
}
