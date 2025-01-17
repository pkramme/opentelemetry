<?php

declare(strict_types=1);

namespace Shopware\OpenTelemetry\Tests\Integration;

use OpenTelemetry\API\Metrics\MeterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Shopware\OpenTelemetry\Feature;
use Shopware\OpenTelemetry\Metrics\Transports\OpenTelemetryMetricTransport;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Shopware\OpenTelemetry\OpenTelemetryShopwareBundle;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @phpstan-import-type Config from OpenTelemetryShopwareBundle
 */
#[CoversClass(OpenTelemetryShopwareBundle::class)]
#[UsesClass(Feature::class)]
class OpenTelemetryShopwareBundleTest extends TestCase
{
    public function testServicesLoadedWhenMetricsEnabled(): void
    {
        $config = ['metrics' => ['enabled' => true, 'namespace' => 'io.opentelemetry.contrib.php.shopware']];
        $container = $this->loadBundleWithConfig($config);

        if (Feature::metricsSupported()) {
            $this->assertTrue($container->has(MeterInterface::class));
            $this->assertTrue($container->has(OpenTelemetryMetricTransport::class));
        } else {
            $this->assertFalse($container->has(MeterInterface::class));
        }
    }

    public function testServicesNotLoadedWhenMetricsDisabled(): void
    {
        $config = ['metrics' => ['enabled' => false, 'namespace' => 'io.opentelemetry.contrib.php.shopware']];
        $container = $this->loadBundleWithConfig($config);

        $this->assertFalse($container->has(MeterInterface::class));
        $this->assertFalse($container->has(OpenTelemetryMetricTransport::class));
    }

    /**
     * @param Config $config
     */
    private function loadBundleWithConfig(array $config): ContainerBuilder
    {
        $bundle = new OpenTelemetryShopwareBundle();
        $builder = new ContainerBuilder();
        $fileLocator = new FileLocator(__DIR__);
        $phpFileLoader = new PhpFileLoader($builder, $fileLocator);
        $instanceof = [];
        $configurator = new ContainerConfigurator($builder, $phpFileLoader, $instanceof, __DIR__, __DIR__);

        $bundle->loadExtension($config, $configurator, $builder);

        return $builder;
    }
}
