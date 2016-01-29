<?php


namespace Fludio\ApiAdminBundle\Tests\Dummy\app;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fludio\ApiAdminBundle\FludioApiAdminBundle;
use Fludio\FactrineBundle\FludioFactrineBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new JMSSerializerBundle(),
            new FludioFactrineBundle(),
            new FludioApiAdminBundle()
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config.yml');
    }
}
