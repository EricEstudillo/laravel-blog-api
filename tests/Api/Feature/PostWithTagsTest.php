<?php

namespace Tests\Api\Feature;

use App\Http\Controllers\Api\Helpers\AllowedActions;
use App\Http\Controllers\Api\Helpers\PostResponseBuilder;
use App\Post;
use App\Tag;
use function array_chunk;
use function array_map;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use function in_array;
use Tests\Api\Traits\ClientCredentialsValidation;
use Tests\Api\Traits\PostDataBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;

class PostWithTagsTest extends TestCase
{
    protected $endPoint = '/api/posts';

    use DatabaseMigrations;
    use ClientCredentialsValidation;
    use PostDataBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->mockPassportClientValidation();
        $this->responseBuilder = new PostResponseBuilder();
    }

    /** @test */
    public function add_post_with_tag()
    {
        $tag1 = $this->create(Tag::class);
        $tag2 = $this->create(Tag::class);
        $post = factory(Post::class)->make();

        $data = $this->createPostDataStructure($post, ['publish_at'], [$tag1->id, $tag2->id]);
        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_CREATED);

        unset($data['tag_id']);
        $this->assertDatabaseHas('posts', $data);
    }

    /** @test */
    public function add_post_with_invalid_tag()
    {
        $post = factory(Post::class)->make();
        $nonExistentTags = [100, 200];
        $data = $this->createPostDataStructure($post, [], $nonExistentTags);
        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        unset($data['tag_id']);
        $this->assertDatabaseMissing('posts', $data);
    }

    /** @test */
    public function add_post_with_partially_invalid_tag()
    {
        $nonExistentTags = [100, 200];
        $tag1 = $this->create(Tag::class);
        $post = factory(Post::class)->make();
        $data = $this->createPostDataStructure($post, [], array_merge([$tag1->id], $nonExistentTags));

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        unset($data['tag_id']);
        $this->assertDatabaseMissing('posts', $data);
    }

    /** @test */
    public function edit_post_with_tags()
    {
        $post = $this->create(Post::class);
        $tagIds = array_map(function ($tag) {
            return $tag['id'];
        }, $this->create(Tag::class, [], 4)->toArray());
        list($oldTagIds, $newTagIds) = array_chunk($tagIds, 2);

        $post->tags()->attach($oldTagIds);

        $data = $this->createPostDataStructure($post, ['publish_at'], $newTagIds);

        $response = $this->putJson($this->getSpecificEndPoint($post->id), $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::UPDATE)))
            ->assertStatus(Response::HTTP_CREATED);

        $responseTagIds = json_decode($response->content())->data->post->tags;

        $this->assertEquals(2, count($responseTagIds));
        foreach ($responseTagIds as $tagId) {
            $this->assertFalse(in_array($tagId->id, $oldTagIds));
            $this->assertTrue(in_array($tagId->id, $newTagIds));
        }
    }

    /** @test */
    public function edit_post_with_partially_invalid_tag()
    {
        $nonExistentTags = [100, 200];
        $post = $this->create(Post::class);
        $tagIds = array_map(function ($tag) {
            return $tag['id'];
        }, $this->create(Tag::class, [], 4)->toArray());
        list($oldTagIds, $newTagIds) = array_chunk($tagIds, 2);

        $post->tags()->attach($oldTagIds);

        $data = $this->createPostDataStructure($post, [], array_merge($newTagIds, $nonExistentTags));

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function edit_post_with_invalid_tag()
    {
        $nonExistentTags = [100, 200];
        $post = $this->create(Post::class);
        $tagIds = array_map(function ($tag) {
            return $tag['id'];
        }, $this->create(Tag::class, [], 2)->toArray());

        $post->tags()->attach($tagIds);

        $data = $this->createPostDataStructure($post, [], $nonExistentTags);

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function delete_post()
    {
        $post = $this->create(Post::class);

        $data = $this->createPostDataStructure($post, ['tag_id']);
        $this->assertDatabaseHas('posts', $data);

        $this->deleteJson($this->getSpecificEndPoint($post->id))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_ACCEPTED, AllowedActions::DESTROY)))
            ->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseMissing('posts', $data);
    }
}