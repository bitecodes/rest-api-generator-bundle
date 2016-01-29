<?php

namespace Fludio\ApiAdminBundle\Annotation;

use Fludio\ApiAdminBundle\Handler\FormHandler;
use Fludio\ApiAdminBundle\Resource\ResourceActionData;
use Fludio\ApiAdminBundle\Resource\ResourceManager;
use Fludio\ApiAdminBundle\Resource\Resource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

class GenerateApiDocHandler implements HandlerInterface
{
    /**
     * @var ResourceManager
     */
    private $manager;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ResourceManager $manager, ContainerInterface $container)
    {
        $this->manager = $manager;
        $this->container = $container;
    }

    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        $resource = $this->getResource($route);

        foreach ($annotations as $annot) {
            if ($annot instanceof GenerateApiDoc) {
                $annotation->setSection(ucwords($resource->getName()));
                if ($this->returnsEntity($route)) {
                    $this->setOutput($annotation, $resource);
                }

                if ($this->expectsInput($route)) {
                    $this->setInput($annotation, $resource);
                }
            }
        }
    }

    private function getResource(Route $route)
    {
        $entity = $route->getDefault('_entity');

        foreach ($this->manager->getConfigurations() as $resource) {
            if ($resource->getEntityNamespace() == $entity) {
                return $resource;
            }
        }
    }

    private function setOutput(ApiDoc $annotation, Resource $resource)
    {
        $refl = new \ReflectionClass($annotation);

        $prop = $refl->getProperty('output');

        $prop->setAccessible(true);
        $prop->setValue($annotation, $resource->getEntityNamespace());
        $prop->setAccessible(false);
    }

    private function setInput(ApiDoc $annotation, Resource $resource)
    {
        $refl = new \ReflectionClass($annotation);

        /** @var FormHandler $formHandler */
        $formHandler = $this->container->get($resource->getServices()->getFormHandlerServiceName());

        $prop = $refl->getProperty('input');

        $prop->setAccessible(true);
        $prop->setValue($annotation, $formHandler->getFormTypeClass());
        $prop->setAccessible(false);
    }

    private function returnsEntity(Route $route)
    {
        $methods = $route->getMethods();

        if (in_array('GET', $methods) ||
            in_array('POST', $methods) ||
            in_array('PUT', $methods) ||
            in_array('PATCH', $methods)
        ) {
            return true;
        }

        return false;
    }

    private function expectsInput(Route $route)
    {
        $methods = $route->getMethods();

        if (in_array('POST', $methods) ||
            in_array('PUT', $methods) ||
            in_array('PATCH', $methods)
        ) {
            return true;
        }

        return false;
    }
}
