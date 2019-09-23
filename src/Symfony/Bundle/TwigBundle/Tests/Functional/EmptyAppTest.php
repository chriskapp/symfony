<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\TwigBundle\Tests\Functional;

use Symfony\Bundle\TwigBundle\Tests\TestCase;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ErrorRenderer\ErrorRenderer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class EmptyAppTest extends TestCase
{
    public function testBootEmptyApp()
    {
        $kernel = new EmptyAppKernel('test', true);
        $kernel->boot();

        $this->assertTrue($kernel->getContainer()->hasParameter('twig.default_path'));
        $this->assertNotEmpty($kernel->getContainer()->getParameter('twig.default_path'));
    }

    protected function setUp()
    {
        $this->deleteTempDir();
    }

    protected function tearDown()
    {
        $this->deleteTempDir();
    }

    private function deleteTempDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.Kernel::VERSION.'/EmptyAppKernel')) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }
}

class EmptyAppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [new TwigBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(static function (ContainerBuilder $container) {
            $container->loadFromExtension('twig', [ // to be removed in 5.0 relying on default
                'strict_variables' => false,
                'exception_controller' => null,
            ]);
            $container->register('error_renderer', ErrorRenderer::class);
            $container->setParameter('debug.file_link_format', null);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/EmptyAppKernel/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/EmptyAppKernel/logs';
    }
}
