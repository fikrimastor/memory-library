<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EmbeddingDriverInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class EmbeddingManager
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected Container $container;

    /**
     * The array of resolved embedding drivers.
     *
     * @var array<string, \App\Contracts\EmbeddingDriverInterface>
     */
    protected array $drivers = [];

    /**
     * Create a new Embedding manager instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get an embedding driver instance.
     *
     * @param  string|null  $name
     * @return \App\Contracts\EmbeddingDriverInterface
     */
    public function driver(string $name = null): EmbeddingDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] = $this->get($name);
    }

    /**
     * Attempt to get the embedding driver from the local cache.
     *
     * @param  string  $name
     * @return \App\Contracts\EmbeddingDriverInterface
     */
    protected function get(string $name): EmbeddingDriverInterface
    {
        return $this->drivers[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given embedding driver.
     *
     * @param  string  $name
     * @return \App\Contracts\EmbeddingDriverInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve(string $name): EmbeddingDriverInterface
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Embedding driver [{$name}] is not defined.");
        }

        $driverMethod = 'create'.ucfirst($name).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException(
            "Driver [{$name}] is not supported."
        );
    }

    /**
     * Create an instance of the OpenAI embedding driver.
     *
     * @param  array  $config
     * @return \App\Contracts\EmbeddingDriverInterface
     */
    public function createOpenaiDriver(array $config): EmbeddingDriverInterface
    {
        // We'll implement the actual driver later
        // For now, we'll use a placeholder
        return new class implements EmbeddingDriverInterface {
            public function embed(string $text): array {
                return array_fill(0, 1536, 0.0);
            }
            
            public function getName(): string {
                return 'openai';
            }
            
            public function isHealthy(): bool {
                return true;
            }
            
            public function getDimensions(): int {
                return 1536;
            }
        };
    }

    /**
     * Create an instance of the CloudFlare embedding driver.
     *
     * @param  array  $config
     * @return \App\Contracts\EmbeddingDriverInterface
     */
    public function createCloudflareDriver(array $config): EmbeddingDriverInterface
    {
        // We'll implement the actual driver later
        // For now, we'll use a placeholder
        return new class implements EmbeddingDriverInterface {
            public function embed(string $text): array {
                return array_fill(0, 768, 0.0);
            }
            
            public function getName(): string {
                return 'cloudflare';
            }
            
            public function isHealthy(): bool {
                return true;
            }
            
            public function getDimensions(): int {
                return 768;
            }
        };
    }

    /**
     * Create an instance of the Cohere embedding driver.
     *
     * @param  array  $config
     * @return \App\Contracts\EmbeddingDriverInterface
     */
    public function createCohereDriver(array $config): EmbeddingDriverInterface
    {
        // We'll implement the actual driver later
        // For now, we'll use a placeholder
        return new class implements EmbeddingDriverInterface {
            public function embed(string $text): array {
                return array_fill(0, 1024, 0.0);
            }
            
            public function getName(): string {
                return 'cohere';
            }
            
            public function isHealthy(): bool {
                return true;
            }
            
            public function getDimensions(): int {
                return 1024;
            }
        };
    }

    /**
     * Get the embedding driver configuration.
     *
     * @param  string  $name
     * @return array|null
     */
    protected function getConfig(string $name): ?array
    {
        return $this->container['config']["embedding.providers.{$name}"];
    }

    /**
     * Get the default embedding driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->container['config']['embedding.default'];
    }

    /**
     * Set the default embedding driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        $this->container['config']['embedding.default'] = $name;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend(string $driver, \Closure $callback): static
    {
        // We'll implement this later if needed
        return $this;
    }
}