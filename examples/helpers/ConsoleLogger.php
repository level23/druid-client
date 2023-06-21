<?php
declare(strict_types=1);

use Psr\Log\LoggerInterface;

class ConsoleLogger implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string       $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array<mixed>  $context
     *
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array<mixed>  $context
     *
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array<mixed>  $context
     *
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array<mixed>  $context
     *
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array<mixed>  $context
     *
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        echo $level . ' - ' . $message . PHP_EOL;
        print_r($context);
    }
}