<?php

namespace Fludio\RestApiGeneratorBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Fludio\RestApiGeneratorBundle\Handler\BaseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDoc;

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

        return $this->getHandler()->{$_indexGetterMethod}($params);
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
            throw new EntityNotFoundException;
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
            throw new EntityNotFoundException;
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
}
