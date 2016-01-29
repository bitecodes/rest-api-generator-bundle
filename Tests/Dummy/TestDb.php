<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Dummy;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;

class TestDb
{
    /**
     * @var \Doctrine\ORM\Configuration
     */
    private $doctrineConfig;

    /**
     * @var array
     */
    private $connectionOptions;

    /**
     * @param string $annotationPath
     * @param string $proxyDir
     * @param string $proxyNamespace
     */
    public function __construct($annotationPath, $proxyDir, $proxyNamespace)
    {
        $cache = new ArrayCache();

        $config = new Configuration();
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setMetadataDriverImpl(
            $config->newDefaultAnnotationDriver([$annotationPath], false)
        );
        $config->setProxyDir($proxyDir);
        $config->setProxyNamespace($proxyNamespace);
        $config->setAutoGenerateProxyClasses(true);

        $this->connectionOptions = array(
            'driver' => 'pdo_sqlite',
            'path' => ':memory:'
        );

        $this->doctrineConfig = $config;
    }

    /**
     * @return EntityManager
     */
    public function createEntityManager()
    {
        $em = EntityManager::create($this->connectionOptions,
            $this->doctrineConfig);
        $this->createSchema($em);

        return $em;
    }

    /**
     * @param EntityManager $em
     */
    private function createSchema(EntityManager $em)
    {
        $tool = new SchemaTool($em);
        $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }
}