<?php

namespace Fludio\ApiAdminBundle\Configuration;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Convention
{
    /**
     * @var string
     */
    protected $bundlePrefix;

    /**
     * Convention constructor.
     * @param array $conventions
     */
    public function __construct(array $conventions)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'bundlePrefix' => $this->getDefaultBundlePrefix()
        ]);
        $conventions = $resolver->resolve($conventions);

        $this->bundlePrefix = $conventions['bundlePrefix'];
    }

    /**
     * Get the resource name of an entity
     *
     * @param $entity
     * @return string
     */
    public function getResourceName($entity)
    {
        $refl = new \ReflectionClass($entity);

        return Inflector::tableize($refl->getShortName());
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return $this->bundlePrefix;
    }

    private function getDefaultBundlePrefix()
    {
        return 'fludio.api_admin';
    }
}