<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Serialization\FieldsListExclusionStrategy;

use BiteCodes\RestApiGeneratorBundle\Serialization\FieldsListExclusionStrategy;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Comment;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

class FieldsListExclusionStrategyTest extends TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;
    /**
     * @var Post
     */
    protected $post;

    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testFieldListsExclusionStrategy', true);
        $kernel->setConfigFile('config_simple.yml');
        return $kernel;
    }

    public function setUp()
    {
        parent::setUp();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        $this->serializer = SerializerBuilder::create()->build();

        $category = $this->factory->make(Category::class, ['name' => 'My category']);
        $comments = $this->factory->times(2)->create(Comment::class, [
            'body' => 'some content'
        ]);
        $this->post = $this->factory->create(Post::class, [
            'title' => 'My Post',
            'content' => 'bla',
            'category' => $category,
            'comments' => $comments
        ]);
        foreach ($comments as $comment) {
            $comment->setPost($this->post);
        }
    }

    /** @test */
    public function it_returns_only_fields_that_are_specified()
    {
        $context = $this->getContextWithFields(['id', 'title']);

        $data = $this->serialize($this->post, $context);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayNotHasKey('content', $data);
    }

    /** @test */
    public function if_no_fields_are_specified_all_fields_will_be_returned()
    {
        $context = $this->getContextWithFields([]);

        $data = $this->serialize($this->post, $context);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('content', $data);
    }

    /** @test */
    public function fields_of_embedded_entities_can_be_specified()
    {
        $context = $this->getContextWithFields(['title', 'category' => ['name']]);

        $data = $this->serialize($this->post, $context);

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArraySubset(['category' => ['name' => 'My category']], $data);
        $this->assertArrayNotHasKey('id', $data['category']);
    }

    /** @test */
    public function multiple_embedded_fields_can_be_specified()
    {
        $context = $this->getContextWithFields(['title', 'category' => ['name'], 'comments' => ['body']]);

        $data = $this->serialize($this->post, $context);

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArraySubset(['category' => ['name' => 'My category']], $data);
        $this->assertArrayHasKey('comments', $data);
        $this->assertArraySubset(['comments' => [
            ['body' => 'some content'],
            ['body' => 'some content'],
        ]], $data);
        $this->assertArrayNotHasKey('id', $data['category']);
        $this->assertArrayNotHasKey('id', $data['comments'][0]);
    }

    /** @test */
    public function it_handles_deeply_nested_fields()
    {
        $comment = $this->post->getComments()[0];

        $context = $this->getContextWithFields(['body', 'post' => ['id', 'title', 'category' => ['name']]]);

        $data = $this->serialize($comment, $context);

        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayHasKey('body', $data);
        $this->assertArrayHasKey('post', $data);
        $this->assertArraySubset([
            'post' => [
                'id' => 1,
                'title' => 'My Post',
                'category' => [
                    'name' => 'My category'
                ]
            ]
        ], $data);
        $this->assertArrayNotHasKey('content', $data['post']);
        $this->assertArrayNotHasKey('id', $data['post']['category']);
    }

    /**
     * @param $fields
     * @return SerializationContext
     */
    protected function getContextWithFields($fields)
    {
        $context = new SerializationContext();

        $context->setGroups(['Default', 'Detail']);

        $context->addExclusionStrategy(new FieldsListExclusionStrategy($fields));

        return $context;
    }

    /**
     * @param $entity
     * @param $context
     * @return mixed
     */
    protected function serialize($entity, $context)
    {
        $jsonContent = $this->serializer->serialize($entity, 'json', $context);

        return json_decode($jsonContent, true);
    }
}
