<?php


namespace Fludio\RestApiGeneratorBundle\Tests\Dummy\app;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fludio\FactrineBundle\FludioFactrineBundle;
use Fludio\RestApiGeneratorBundle\FludioRestApiGeneratorBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    protected $configFile;

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new DoctrineBundle(),
            new JMSSerializerBundle(),
            new FludioFactrineBundle(),
            new FludioRestApiGeneratorBundle()
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }

    /**
     * @param mixed $configFile
     */
    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }
}
