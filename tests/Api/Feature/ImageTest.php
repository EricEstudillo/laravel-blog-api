<?php

namespace Tests\Api\Feature;

use App\Http\Controllers\Api\Helpers\AllowedActions;
use App\Http\Controllers\Api\Helpers\ImageResponseBuilder;
use App\Http\Controllers\Api\ImageController;
use App\Image;
use App\Post;
use function factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use function json_encode;
use Tests\Api\Traits\ClientCredentialsValidation;
use Tests\Api\Traits\ErrorFieldsValidation;
use Tests\TestCase;
use function urlencode;

class ImageTest extends TestCase
{
    protected $endPoint = '/api/images';
    /** @var ImageResponseBuilder */
    private $responseBuilder;

    use DatabaseMigrations;
    use ClientCredentialsValidation;
    use ErrorFieldsValidation;

    protected function setUp()
    {
        parent::setUp();
        $this->mockPassportClientValidation();
        $this->responseBuilder = new ImageResponseBuilder();
    }

    /** @test */
    public function list_images()
    {
        $p1 = $this->create(Image::class);
        $p2 = $this->create(Image::class);

        $this->assertDatabaseHas('images', ['name' => $p1->name, 'name' => $p2->name]);

        $this->getJson($this->endPoint)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee($p1->name)
            ->assertSee($p2->name);
    }

    /** @test */
    public function show_image()
    {
        $image = $this->create(Image::class);

        $this->getJson($this->getSpecificEndPoint($image->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee($image->name)
            ->assertSee($image->path)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)));
    }

    /** @test */
    public function show_non_existent_image()
    {
        $this->assertDatabaseMissing('images', ['id' => $this->nonExistentId]);
        $this->getJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function add_image()
    {
        $post = $this->create(Post::class);
        $image = factory(Image::class)->make();
        $data = self::createImageDataStructure($image, $post->id);
        $responseMessage = $this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::STORE);

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($responseMessage))
            ->assertStatus(Response::HTTP_CREATED);

        $data['path'] = urlencode($data['path']);
        $this->assertDatabaseHas('images', $data);
    }

    /** @test */
    public function add_image_with_invalid_post()
    {
        $image = factory(Image::class)->make();
        $data = self::createImageDataStructure($image, $this->nonExistentId);

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function add_invalid_image()
    {
        $data = self::emptyImageDataStructure();
        $response = $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE)))
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertShowErrorFields(ImageController::class, $response);
    }

    /** @test */
    public function edit_image()
    {
        $posts = $this->create(Post::class, [], 2);
        $image = $this->create(Image::class, ['post_id' => $posts->get(0)->id]);
        $imageOld = clone($image);
        $image->name = 'updatedName';
        $image->path = $image->path . '_updated';

        $data = self::createImageDataStructure($image, $posts->get(1)->id);

        $this->putJson($this->getSpecificEndPoint($image->id), $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::UPDATE)))
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('images', $data);

        $data = self::createImageDataStructure($imageOld, $posts->get(0)->id);
        $this->assertDatabaseMissing('images', $data);
    }

    /** @test */
    public function edit_non_existent_image()
    {
        $this->assertDatabaseMissing('images', ['id' => $this->nonExistentId]);
        $this->putJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function edit_image_with_invalid_data()
    {
        $post = $this->create(Post::class);
        $image = $this->create(Image::class, ['post_id' => $post->id]);

        $emptyData = self::emptyImageDataStructure();
        $response = $this->putJson($this->getSpecificEndPoint($image->id), $emptyData)
            ->assertSee(
                json_encode($this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::UPDATE))
            )
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $data = self::createImageDataStructure($image, $post->id);
        $this->assertDatabaseHas('images', $data);

        $this->assertShowErrorFields(ImageController::class, $response);
    }

    /** @test */
    public function delete_image()
    {
        $post = $this->create(Post::class);
        $image = $this->create(Image::class, ['post_id' => $post->id]);

        $data = self::createImageDataStructure($image, $post->id);
        $this->assertDatabaseHas('images', $data);

        $this->deleteJson($this->getSpecificEndPoint($image->id))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_ACCEPTED, AllowedActions::DESTROY)))
            ->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseMissing('images', $data);
    }

    /** @test */
    public function delete_non_existent_image()
    {
        $this->assertDatabaseMissing('images', ['id' => $this->nonExistentId]);

        $this->deleteJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public static function emptyImageDataStructure(): array
    {
        return [
            'name' => '',
            'path' => '',
            'post_id' => '',
        ];
    }

    public static function createImageDataStructure(Image $image, $postId): array
    {
        $data = self::emptyImageDataStructure();
        $data['name'] = $image->name;
        $data['path'] = $image->path;
        $data['post_id'] = $postId;

        return $data;
    }
}
