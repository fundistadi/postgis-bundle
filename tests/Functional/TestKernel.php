<?php

declare(strict_types=1);

namespace FundiStadi\PostGIS\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FundiStadi\PostGIS\FundiPostGISBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new FundiPostGISBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 2);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/fundi_postgis/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/fundi_postgis/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'test' => true,
            'php_errors' => ['log' => true],
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'url' => '%env(DATABASE_URL)%',
            ],
            'orm' => [
                'controller_resolver' => ['auto_mapping' => false],
                'mappings' => [
                    'Fixtures' => [
                        'type' => 'attribute',
                        'dir' => \dirname(__DIR__).'/Integration/Fixtures',
                        'prefix' => 'FundiStadi\\PostGIS\\Tests\\Integration\\Fixtures',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // No routes needed for the wiring test.
    }
}
