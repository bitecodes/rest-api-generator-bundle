<?php

namespace Fludio\RestApiGeneratorBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Fludio\RestApiGeneratorBundle\Api\Response\ApiProblem;
use Fludio\RestApiGeneratorBundle\Exception\ApiProblemException;
use Fludio\RestApiGeneratorBundle\Handler\BaseHandler;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RestApiController extends Controller
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
        $this->checkForAccess();

        $params = $request->query->all();

        $page = !empty($params['page']) ? $params['page'] : null;
        $limit = !empty($params['limit']) ? $params['limit'] : null;

        $paginator = null;
        $result = $this->getHandler()->{$_indexGetterMethod}($params, $page, $limit, $paginator);

        if ($paginator instanceof Pagerfanta) {
            $this->addLinksToMetadata($paginator, $request->get('_route'), $limit);
        }

        return $result;
    }

    /**
     * Get an entity by id.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function showAction($id)
    {
        $this->checkForAccess();

        return $this->getEntityOrThrowException($id);
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
        $this->checkForAccess();

        return $this->getHandler()->post($request->request->all());
    }

    /**
     * Update an entity.
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param Request $request the request object
     * @param $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function updateAction(Request $request, $id)
    {
        $this->checkForAccess();

        $entity = $this->getEntityOrThrowException($id);
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
        $this->checkForAccess();

        $entities = $this->getEntitiesOrThrowException($request->get('id'));

        $this->getHandler()->batchUpdate($entities, $request->request->all(), $request->getMethod());

        return $entities;
    }

    /**
     * Delete an entity
     *
     * @ApiDoc()
     * @GenerateApiDoc()
     *
     * @param $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function deleteAction($id)
    {
        $this->checkForAccess();

        $entity = $this->getEntityOrThrowException($id);

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
        $this->checkForAccess();

        $this->getHandler()->batchDelete($request->get('id'));

        return [];
    }

    /**
     * @param $id
     * @return null|object
     * @throws EntityNotFoundException
     */
    protected function getEntityOrThrowException($id)
    {
        if (null === $entity = $this->getHandler()->get($id)) {
            $problem = new ApiProblem(
                404,
                ApiProblem::TYPE_ENTITY_NOT_FOUND
            );
            throw new ApiProblemException($problem);
        }

        return $entity;
    }

    /**
     * @param $ids
     * @return array
     * @throws EntityNotFoundException
     */
    protected function getEntitiesOrThrowException($ids)
    {
        $entites = $this->getHandler()->getBy(['id' => $ids]);

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
    protected function getHandler()
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
     * Check if user has permission to access action
     */
    protected function checkForAccess()
    {
        $roles = $this->get('request_stack')->getCurrentRequest()->get('_roles');

        if ($roles) {
            $this->denyAccessUnlessGranted($roles);
        }
    }

    /**
     * @param Pagerfanta $paginator
     * @param $route
     * @param $limit
     */
    protected function addLinksToMetadata(Pagerfanta $paginator, $route, $limit)
    {
        $data = $this->get('fludio_rest_api_generator.services.response_data');
        $router = $this->get('router');

        $data->addLink('first', $router->generate($route, ['page' => 1, 'limit' => $limit], UrlGeneratorInterface::NETWORK_PATH));
        $data->addLink('prev', $router->generate($route, ['page' => $paginator->getPreviousPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL));
        $data->addLink('current', $router->generate($route, ['page' => $paginator->getCurrentPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL));
        $data->addLink('next', $router->generate($route, ['page' => $paginator->getNextPage(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL));
        $data->addLink('last', $router->generate($route, ['page' => $paginator->getNbPages(), 'limit' => $limit], UrlGeneratorInterface::ABSOLUTE_URL));

        $data->addMeta('total', $paginator->getNbResults());
    }
}
