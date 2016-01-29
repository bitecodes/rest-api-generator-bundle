<?php

namespace Fludio\RestApiGeneratorBundle\Annotation;

use Doctrine\Common\Inflector\Inflector;
use Fludio\RestApiGeneratorBundle\Handler\FormHandler;
use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Yaml\Inline;

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
                if ($roles = $route->getDefault('_roles')) {
                    $annotation->setAuthentication(true);
                    $annotation->setAuthenticationRoles($roles);
                }
                $annotation->setDescription($this->getDescription($resource, $route));
                $annotation->setDocumentation($this->getDescription($resource, $route));
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

    private function getDescription(Resource $resource, Route $route)
    {
        $description = '';

        $name = $resource->getName();

        $action = $resource->getActions()->getActionFromRoute($route);

        switch ($action) {
            case ResourceActionData::ACTION_INDEX:
                $description = 'List all ' . Inflector::pluralize($name);
                break;
            case ResourceActionData::ACTION_SHOW:
                $description = 'Get a single ' . Inflector::singularize($name);
                break;
            case ResourceActionData::ACTION_CREATE:
                $description = 'Create a new ' . Inflector::singularize($name);
                break;
            case ResourceActionData::ACTION_UPDATE:
                $description = 'Update a ' . Inflector::singularize($name);
                break;
            case ResourceActionData::ACTION_BATCH_UPDATE:
                $description = 'Update multiple ' . Inflector::pluralize($name);
                break;
            case ResourceActionData::ACTION_DELETE:
                $description = 'Delete a ' . Inflector::singularize($name);
                break;
            case ResourceActionData::ACTION_BATCH_DELETE:
                $description = 'Delete multiple ' . Inflector::pluralize($name);
                break;
        }

        return $description;
    }
}
