<?php

namespace Tests\Api\Feature;

use App\Http\Controllers\Api\Helpers\AllowedActions;
use App\Http\Controllers\Api\Helpers\PostResponseBuilder;
use App\Http\Controllers\Api\PostController;
use App\Image;
use App\Post;
use Carbon\Carbon;
use DateTime;
use function factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Api\Traits\ClientCredentialsValidation;
use Tests\Api\Traits\ErrorFieldsValidation;
use Tests\Api\Traits\PostDataBuilder;
use Tests\Api\Traits\SQLiteForeignKey;
use Tests\TestCase;
use Illuminate\Http\Response;

class PostTest extends TestCase
{
    protected $endPoint = '/api/posts';
    private $responseBuilder;
    private $validIsoDate = "2019-02-01T15:00:00-01:00";

    use DatabaseMigrations;
    use ClientCredentialsValidation;
    use ErrorFieldsValidation;
    use PostDataBuilder;
    use SQLiteForeignKey;

    protected function setUp()
    {
        parent::setUp();
        $this->mockPassportClientValidation();
        $this->responseBuilder = new PostResponseBuilder();
        $this->activateForeignKeysOption();
    }

    /** @test */
    public function list_posts()
    {
        $p1 = $this->create(Post::class);
        $p2 = $this->create(Post::class);

        $this->assertDatabaseHas('posts', ['title' => $p1->title, 'title' => $p2->title]);
        $this->getJson($this->endPoint)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee($p1->title)
            ->assertSee($p2->title);
    }

    /** @test */
    public function show_post()
    {
        $post = $this->create(Post::class);
        $this->getJson($this->getSpecificEndPoint($post->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)))
            ->assertSee($post->title)
            ->assertSee($post->body)
            ->assertSee($post->user_id);
    }

    /** @test */
    public function show_non_existent_post()
    {
        $this->assertDatabaseMissing('posts', ['id' => $this->nonExistentId]);
        $this->getJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function add_post()
    {
        $post = factory(Post::class)->make(['publish_at' => $this->validIsoDate]);

        $data = $this->createPostDataStructure($post);
        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_CREATED);

        unset($data['tag_id']);
        $validDate = Carbon::createFromFormat(DateTime::ATOM, $this->validIsoDate);
        $data['publish_at'] = $validDate->tz('UTC')->toDateTimeString();
        $this->assertDatabaseHas('posts', $data);
    }

    /** @test */
    public function add_post_with_duplicated_slug()
    {
        $oldPost = $this->create(Post::class);
        $post = factory(Post::class)->make(['title' => $oldPost->title]);

        $data = $this->createPostDataStructure($post, ['publish_at']);
        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_CREATED);

        unset($data['tag_id']);
        $data['slug'] = str_slug($post->title) . '-1';
        $this->assertDatabaseHas('posts', $data);
    }

    /** @test */
    public function add_post_with_invalid_params()
    {
        $data = $this->emptyPostDataStructure();
        $response = $this->postJson($this->endPoint, $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee(
                json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE))
            );
        $this->assertShowErrorFields(PostController::class, $response);
    }


    /** @test */
    public function edit_post()
    {
        $post = $this->create(Post::class, ['publish_at' => $this->validIsoDate]);
        $oldPost = clone($post);

        $modifiedDate = Carbon::createFromFormat(DateTime::ATOM, $this->validIsoDate);
        $post->title = 'modified title';
        $post->body = 'modified body';
        $post->publish_at = $modifiedDate->addDay()->toIso8601String();

        $data = $this->createPostDataStructure($post);
        $this->putJson($this->getSpecificEndPoint($post->id), $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::UPDATE)))
            ->assertStatus(Response::HTTP_CREATED);

        unset($data['tag_id']);
        $data['publish_at'] = $modifiedDate->tz('UTC')->toDateTimeString();
        $this->assertDatabaseHas('posts', $data);

        $data = $this->createPostDataStructure($oldPost);
        $this->assertDatabaseMissing('posts', $data);
    }

    /** @test */
    public function edit_non_existent_post()
    {
        $this->assertDatabaseMissing('posts', ['id' => $this->nonExistentId]);
        $this->putJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function edit_post_with_invalid_data()
    {
        $post = $this->create(Post::class);

        $data = $this->emptyPostDataStructure();
        $this->putJson($this->getSpecificEndPoint($post->id), $data)
            ->assertSee(
                json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::UPDATE))
            )
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $data = $this->createPostDataStructure($post);
        unset($data['tag_id']);
        $this->assertDatabaseHas('posts', $data);
    }

    /** @test */
    public function delete_post()
    {
        $post = $this->create(Post::class);
        $image = $this->create(Image::class, ['post_id' => $post->id]);

        $data = $this->createPostDataStructure($post, ['tag_id']);
        $imageData = ImageTest::createImageDataStructure($image, $post->id);

        $this->assertDatabaseHas('posts', $data);
        $this->assertDatabaseHas('images', $imageData);

        $this->deleteJson($this->getSpecificEndPoint($post->id))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_ACCEPTED, AllowedActions::DESTROY)))
            ->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseMissing('posts', $data);
        $this->assertDatabaseMissing('images', $imageData);
    }

    /** @test */
    public function delete_non_existent_post()
    {
        $this->assertDatabaseMissing('posts', ['id' => $this->nonExistentId]);

        $this->deleteJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}