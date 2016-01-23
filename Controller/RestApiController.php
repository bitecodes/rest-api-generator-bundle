<?php

namespace Fludio\ApiAdminBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Fludio\ApiAdminBundle\Handler\BaseHandler;
use Symfony\Component\HttpFoundation\Request;

class RestApiController
{
    /**
     * @var BaseHandler
     */
    private $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * List all entities.
     *
     * @return array
     */
    public function indexAction()
    {
        return $this->handler->all();
    }

    /**
     * Get an entity by id.
     *
     * @return array
     */
    public function showAction($id)
    {
        return $this->getEntityOrThrowException($id);
    }

    /**
     * Create a new entity.
     *
     * @param Request $request the request object
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->handler->post($request->request->all());
    }

    /**
     * Update an entity.
     *
     * @param Request $request the request object
     * @param $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->getEntityOrThrowException($id);
        return $this->handler->update($entity, $request->request->all(), $request->getMethod());
    }

    /**
     * Update an entity.
     *
     * @param Request $request the request object
     * @return array
     * @throws EntityNotFoundException
     */
    public function batch_updateAction(Request $request)
    {
        $entities = $this->getEntitiesOrThrowException($request->get('id'));

        $this->handler->batchUpdate($entities, $request->request->all(), $request->getMethod());

        return $entities;
    }

    /**
     * Delete an entity
     *
     * @param $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function deleteAction($id)
    {
        $entity = $this->getEntityOrThrowException($id);

        $this->handler->delete($entity);

        return [];
    }

    public function batch_deleteAction(Request $request)
    {
        $this->handler->batchDelete($request->get('id'));

        return [];
    }

    /**
     * @param $id
     * @return null|object
     * @throws EntityNotFoundException
     */
    protected function getEntityOrThrowException($id)
    {
        if (null === $entity = $this->handler->get($id)) {
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
        $entites = $this->handler->getBy(['id' => $ids]);

        if (count($ids) != count($entites)) {
            throw new EntityNotFoundException;
        }

        return $entites;
    }
}
