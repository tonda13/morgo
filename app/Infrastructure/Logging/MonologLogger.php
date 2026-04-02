<?php

declare(strict_types=1);

namespace Morgo\Infrastructure\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class MonologLogger extends AbstractLogger
{
    private Logger $logger;
    private static array $channels = [];

    public function __construct(
        string $channel = 'morgocms',
        string $logPath = '',
        bool $debug = false
    ) {
        $this->logger = new Logger($channel);

        if (empty($logPath)) {
            $logPath = (defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/../../..') . '/storage/logs/app.log';
        }

        $level = $debug ? Level::Debug : Level::Warning;

        if ($debug) {
            // Dev: stderr + file
            $this->logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));
        }

        // Rotating file handler: 30 days retention
        $this->logger->pushHandler(new RotatingFileHandler($logPath, 30, $level));
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, (string) $message, $context);
    }

    public static function getChannel(string $channel): LoggerInterface
    {
        if (!isset(self::$channels[$channel])) {
            $debug   = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            $logPath = (defined('BASE_PATH') ? BASE_PATH : '') . '/storage/logs/' . $channel . '.log';
            self::$channels[$channel] = new self($channel, $logPath, $debug);
        }
        return self::$channels[$channel];
    }
}
