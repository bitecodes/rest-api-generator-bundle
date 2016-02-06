<?php

namespace Fludio\RestApiGeneratorBundle\Annotation;

use Doctrine\Common\Inflector\Inflector;
use Fludio\DoctrineFilter\FilterBuilder;
use Fludio\DoctrineFilter\FilterInterface;
use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Routing\Route;

class GenerateApiDocHandler implements HandlerInterface
{
    /**
     * @var ResourceManager
     */
    private $manager;

    public function __construct(ResourceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ApiDoc $annotation
     * @param array $annotations
     * @param Route $route
     * @param \ReflectionMethod $method
     */
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

                $this->addFilter($annotation, $resource);
                $this->addPagination($annotation, $resource);
            }
        }
    }

    /**
     * @param Route $route
     * @return mixed
     */
    private function getResource(Route $route)
    {
        $entity = $route->getDefault('_entity');

        foreach ($this->manager->getConfigurations() as $resource) {
            if ($resource->getEntityNamespace() == $entity) {
                return $resource;
            }
        }
    }

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    private function setOutput(ApiDoc $annotation, Resource $resource)
    {
        $refl = new \ReflectionClass($annotation);

        $prop = $refl->getProperty('output');

        $prop->setAccessible(true);
        $prop->setValue($annotation, $resource->getEntityNamespace());
        $prop->setAccessible(false);
    }

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    private function setInput(ApiDoc $annotation, Resource $resource)
    {
        $refl = new \ReflectionClass($annotation);

        $prop = $refl->getProperty('input');

        $prop->setAccessible(true);
        $prop->setValue($annotation, [
            'class' => $resource->getFormTypeClass(),
            'name' => ''
        ]);
        $prop->setAccessible(false);
    }

    /**
     * @param Route $route
     * @return bool
     */
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

    /**
     * @param Route $route
     * @return bool
     */
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

    /**
     * @param Resource $resource
     * @param Route $route
     * @return string
     */
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

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    protected function addFilter(ApiDoc $annotation, Resource $resource)
    {
        $filterClass = $resource->getFilterClass();
        /** @var FilterInterface $filter */
        if ($filterClass) {
            $filter = new $filterClass;
            $builder = new FilterBuilder();
            $filter->buildFilter($builder);
            foreach ($builder->getFilters() as $filterElement) {
                $name = $filterElement->getName();
                $options = $filterElement->getOptions();
                $description = !empty($options['description']) ? $options['description'] : '';
                $requirement = !empty($options['requirement']) ? $options['requirement'] : '';
                $annotation->addFilter($name, compact('description', 'requirement'));
            }
        }
    }

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    protected function addPagination(ApiDoc $annotation, Resource $resource)
    {
        if ($resource->hasPagination()) {
            $annotation->addFilter('offset', [
                'description' => 'Offset'
            ]);

            $annotation->addFilter('limit', [
                'description' => 'Limit'
            ]);
        }
    }
}
