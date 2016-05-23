<?php

namespace BiteCodes\RestApiGeneratorBundle\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class EntitiesHolder
{
    /**
     * @var ArrayCollection
     * @Assert\Valid
     */
    protected $entities;

    public function __construct($entities = [])
    {
        $this->entities = new ArrayCollection($entities);
    }

    /**
     * @return mixed
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param mixed $entity
     */
    public function addEntity($entity)
    {
        $this->entities->add($entity);
    }

    /**
     * @param $entity
     */
    public function removeEntity($entity)
    {
        $this->entities->removeElement($entity);
    }
}