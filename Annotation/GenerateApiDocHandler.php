<?php

namespace Fludio\RestApiGeneratorBundle\Annotation;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManager;
use Fludio\DoctrineFilter\FilterBuilder;
use Fludio\DoctrineFilter\FilterInterface;
use Fludio\RestApiGeneratorBundle\Api\Actions\BatchDelete;
use Fludio\RestApiGeneratorBundle\Api\Actions\BatchUpdate;
use Fludio\RestApiGeneratorBundle\Api\Actions\Create;
use Fludio\RestApiGeneratorBundle\Api\Actions\Delete;
use Fludio\RestApiGeneratorBundle\Api\Actions\Index;
use Fludio\RestApiGeneratorBundle\Api\Actions\Show;
use Fludio\RestApiGeneratorBundle\Api\Actions\Update;
use Fludio\RestApiGeneratorBundle\Form\DynamicFormSubscriber;
use Fludio\RestApiGeneratorBundle\Form\DynamicFormType;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

class GenerateApiDocHandler implements HandlerInterface
{
    /**
     * @var \Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager
     */
    private $manager;
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(ApiManager $manager, EntityManager $em)
    {
        $this->manager = $manager;
        $this->em = $em;
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
                    if ($resource->getFormTypeClass() == DynamicFormType::class) {
                        $entityClass = $resource->getEntityClass();
                        $handler = new DynamicFormSubscriber($this->em, new $entityClass);
                        foreach ($handler->getFields() as $field) {
                            $annotation->addParameter($field, ['dataType' => 'string', 'required' => false]);
                        }
                    } else {
                        $this->setInput($annotation, $resource);
                    }
                }
                if ($roles = $route->getDefault('_roles')) {
                    $annotation->setAuthentication(true);
                    $annotation->setAuthenticationRoles($roles);
                }
                $annotation->setDescription($this->getDescription($resource, $route));
                $annotation->setDocumentation($this->getDescription($resource, $route));

                if ($resource->getActions()->getActionForRoute($route) instanceof Index) {
                    $this->addFilter($annotation, $resource);
                    $this->addPagination($annotation, $resource);
                }
            }
        }
    }

    /**
     * @param Route $route
     * @return ApiResource
     */
    private function getResource(Route $route)
    {
        $entity = $route->getDefault('_entity');

        return $this->manager->getResourceForEntity($entity);
    }

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    private function setOutput(ApiDoc $annotation, ApiResource $resource)
    {
        $refl = new \ReflectionClass($annotation);

        $prop = $refl->getProperty('output');

        $prop->setAccessible(true);
        $prop->setValue($annotation, $resource->getEntityClass());
        $prop->setAccessible(false);
    }

    /**
     * @param ApiDoc $annotation
     * @param Resource $resource
     */
    private function setInput(ApiDoc $annotation, ApiResource $resource)
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
     * @param ApiResource $resource
     * @param Route $route
     * @return string
     */
    private function getDescription(ApiResource $resource, Route $route)
    {
        $description = '';

        $name = $resource->getName();

        $action = $resource->getActions()->getActionForRoute($route);

        switch (get_class($action)) {
            case Index::class:
                $description = 'List all ' . Inflector::pluralize($name);
                break;
            case Show::class:
                $description = 'Get a single ' . Inflector::singularize($name);
                break;
            case Create::class:
                $description = 'Create a new ' . Inflector::singularize($name);
                break;
            case Update::class:
                $description = 'Update a ' . Inflector::singularize($name);
                break;
            case BatchUpdate::class:
                $description = 'Update multiple ' . Inflector::pluralize($name);
                break;
            case Delete::class:
                $description = 'Delete a ' . Inflector::singularize($name);
                break;
            case BatchDelete::class:
                $description = 'Delete multiple ' . Inflector::pluralize($name);
                break;
        }

        return $description;
    }

    /**
     * @param ApiDoc $annotation
     * @param ApiResource $resource
     */
    protected function addFilter(ApiDoc $annotation, ApiResource $resource)
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
     * @param ApiResource $resource
     */
    protected function addPagination(ApiDoc $annotation, ApiResource $resource)
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
