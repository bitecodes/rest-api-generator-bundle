<?php

namespace BiteCodes\RestApiGeneratorBundle\Controller;

use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiSerialization;
use Doctrine\ORM\EntityNotFoundException;
use BiteCodes\RestApiGeneratorBundle\Api\Events\ApiControllerDataEvent;
use BiteCodes\RestApiGeneratorBundle\Api\Events\ApiEvents;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiProblem;
use BiteCodes\RestApiGeneratorBundle\Exception\ApiProblemException;
use BiteCodes\RestApiGeneratorBundle\Handler\BaseHandler;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use BiteCodes\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class RestApiController
 * @package BiteCodes\RestApiGeneratorBundle\Controller
 */
class RestApiController extends Controller implements ApiSerialization
{
    /**
     * @var BaseHandler
     */
    private $handler;

    /**
     * List all entities.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request
     * @param $_indexGetterMethod
     * @return array
     */
    public function indexAction(Request $request, $_indexGetterMethod)
    {
        $params = $request->query->all();

        $page = !empty($params['page']) ? $params['page'] : null;
        $limit = !empty($params['limit']) ? $params['limit'] : null;

        $paginator = null;
        $result = $this->getHandler()->{$_indexGetterMethod}($params, $page, $limit, $paginator);

        if ($paginator instanceof Pagerfanta) {
            $this->addLinksToMetadata($paginator, $request->get('_route'), $limit);
        }

        $this
            ->getEventDispatcher()
            ->dispatch(
                ApiEvents::POST_INDEX,
                new ApiControllerDataEvent($result)
            );

        return $result;
    }

    /**
     * Get an entity by id.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param $entity
     * @return array
     */
    public function showAction($entity)
    {
        return $entity;
    }

    /**
     * Create a new entity.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request the request object
     * @return array
     */
    public function createAction(Request $request)
    {
        if ($request->headers->has('batch') && $request->headers->get('batch')) {
            $data = $this->getHandler()->batchCreate($request->request->all());
        } else {
            $data = $this->getHandler()->create($request->request->all());
        }

        return $data;
    }

    /**
     * Update an entity.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request the request object
     * @param $entity
     * @return array
     */
    public function updateAction(Request $request, $entity)
    {
        return $this->getHandler()->update($entity, $request->request->all(), $request->getMethod());
    }

    /**
     * Update an entity.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request the request object
     * @return array
     * @throws EntityNotFoundException
     */
    public function batch_updateAction(Request $request)
    {
        $ids = array_reduce($request->request->all(), function ($ids, $data) {
            $ids[] = $data['id'];
            return $ids;
        }, []);

        $entities = $this->getEntitiesOrThrowException($ids);

        $this->getHandler()->batchUpdate($entities, $request->request->all(), $request->getMethod());

        return $entities;
    }

    /**
     * Delete an entity
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param $entity
     * @return array
     */
    public function deleteAction($entity)
    {
        $this->getHandler()->delete($entity);

        return [];
    }

    /**
     * Batch delete entites
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request
     * @return array
     */
    public function batch_deleteAction(Request $request)
    {
        $this->getHandler()->batchDelete($request->get('id'));

        return [];
    }

    /**
     * @param $ids
     * @return array
     * @throws EntityNotFoundException
     */
    protected function getEntitiesOrThrowException($ids)
    {
        $entites = $this->getHandler()->getBy($ids);

        if (count($ids) != count($entites)) {
            $problem = new ApiProblem(
                404,
                ApiProblem::TYPE_ENTITY_NOT_FOUND
            );
            throw new ApiProblemException($problem);
        }

        return $entites;
    }

    /**
     * @return BaseHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param BaseHandler $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param Pagerfanta $paginator
     * @param $route
     * @param $limit
     */
    protected function addLinksToMetadata(Pagerfanta $paginator, $route, $limit)
    {
        $data = $this->get('bite_codes_rest_api_generator.services.response_data');
        $router = $this->get('router');

        $first = $router->generate($route, ['page' => 1, 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        $prev = $paginator->hasPreviousPage()
            ? $router->generate($route, ['page' => $paginator->getPreviousPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL)
            : null;
        $current = $router->generate($route, ['page' => $paginator->getCurrentPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);
        $next = $paginator->hasNextPage()
            ? $router->generate($route, ['page' => $paginator->getNextPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL)
            : null;
        $last = $router->generate($route, ['page' => $paginator->getNbPages(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL);

        $data->addLink('first', $first);
        $data->addLink('prev', $prev);
        $data->addLink('current', $current);
        $data->addLink('next', $next);
        $data->addLink('last', $last);
        $data->addMeta('total', $paginator->getNbResults());
    }

    /**
     * @return \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }
}
