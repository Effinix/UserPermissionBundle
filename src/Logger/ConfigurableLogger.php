<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Logger;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ConfigurableLogger implements LoggerInterface
{
    public function __construct(
        #[Autowire(service: 'effinix.user_permission.logger')]
        private LoggerInterface $logger,
    ) {
    }

    private function wrap(\Stringable|string $message): string
    {
        return "[Effinix][UserPermission] $message";
    }

    private function addContext(array $context): array
    {
        return ['origin-bundle' => 'effinix/user-permission-bundle'] + $context;
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($this->wrap($message), $this->addContext($context));
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($this->wrap($message), $this->addContext($context));
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($this->wrap($message), $this->addContext($context));
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->logger->error($this->wrap($message), $this->addContext($context));
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($this->wrap($message), $this->addContext($context));
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($this->wrap($message), $this->addContext($context));
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logger->info($this->wrap($message), $this->addContext($context));
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($this->wrap($message), $this->addContext($context));
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $this->wrap($message), $this->addContext($context));
    }
}
