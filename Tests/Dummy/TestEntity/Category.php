<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity()
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Post[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param Post $post
     * @return $this
     */
    public function addPost(Post $post)
    {
        $this->posts->add($post);

        return $this;
    }

    /**
     * @param Post $post
     * @return $this
     */
    public function removePost(Post $post)
    {
        $this->posts->remove($post);

        return $this;
    }

    /**
     * @return Post[]|ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}
