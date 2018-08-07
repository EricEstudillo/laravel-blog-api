<?php

namespace Tests\Api\Feature;

use App\Http\Controllers\Api\Helpers\AllowedActions;
use App\Http\Controllers\Api\Helpers\TagResponseBuilder;
use App\Http\Controllers\Api\TagController;
use App\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\Api\Traits\ClientCredentialsValidation;
use Tests\Api\Traits\ErrorFieldsValidation;
use Tests\TestCase;

class TagTest extends TestCase
{
    protected $endPoint = '/api/tags';
    protected $responseBuilder;

    use DatabaseMigrations;
    use ClientCredentialsValidation;
    use ErrorFieldsValidation;

    protected function setUp()
    {
        parent::setUp();
        $this->mockPassportClientValidation();
        $this->responseBuilder = new TagResponseBuilder();
    }

    /** @test */
    public function list_tags()
    {
        $t1 = $this->create(Tag::class);
        $t2 = $this->create(Tag::class);

        $this->getJson($this->endPoint)
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)))
            ->assertSee('name', $t1->name)
            ->assertSee('name', $t2->name);

        $this->assertDatabaseHas('tags', ['name' => $t1->name, 'name' => $t2->name]);
    }

    /** @test */
    public function show_tag()
    {
        $tag = $this->create(Tag::class);
        $this->getJson($this->getSpecificEndPoint($tag->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_OK)))
            ->assertSee($tag->name);
    }

    /** @test */
    public function show_non_existent_tag()
    {
        $this->getJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function add_tag()
    {
        $tag = factory(Tag::class)->make();
        $data = $this->createTagDataStructure($tag);

        $responseMessage = $this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::STORE);
        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($responseMessage))
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('tags', $data);
    }

    /** @test */
    public function add_tag_with_invalid_data()
    {
        $data = $this->emptyTagDataStructure();
        $responseMessage = $this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::STORE);
        $response = $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($responseMessage))
            ->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertShowErrorFields(TagController::class, $response);
    }

    /** @test */
    public function add_tag_with_duplicate_slug()
    {
        $tag = $this->create(Tag::class);
        $data = $this->createTagDataStructure($tag);

        $this->postJson($this->endPoint, $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CONFLICT)))
            ->assertStatus(Response::HTTP_CONFLICT);

        $count = Tag::where('slug', $tag->slug)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function edit_tag()
    {
        $tag = $this->create(Tag::class);
        $oldTag = clone($tag);
        $tag->name = 'updatedNameForTag';
        $data = $this->createTagDataStructure($tag);

        $responseMessage = $this->responseBuilder->message(Response::HTTP_CREATED, AllowedActions::UPDATE);
        $this->putJson($this->getSpecificEndPoint($tag->id), $data)
            ->assertSee(json_encode($responseMessage))
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('tags', $data);

        $data = $this->createTagDataStructure($oldTag);
        $this->assertDatabaseMissing('tags', $data);
    }

    /** @test */
    public function edit_nonexistent_tag()
    {
        $this->putJson($this->getSpecificEndPoint( $this->nonExistentId))
            ->assertSee($this->responseBuilder->message(Response::HTTP_NOT_FOUND))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function edit_tag_with_invalid_params()
    {
        $tag = $this->create(Tag::class);

        $data = $this->emptyTagDataStructure();
        $responseMessage = $this->responseBuilder->message(Response::HTTP_BAD_REQUEST, AllowedActions::UPDATE);
        $response = $this->putJson($this->getSpecificEndPoint($tag->id), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSee(json_encode($responseMessage));

        $data = $this->createTagDataStructure($tag);
        $this->assertDatabaseHas('tags', $data);

        $this->assertShowErrorFields(TagController::class, $response);
    }

    /** @test */
    public function edit_tag_with_slug_already_in_db()
    {
        $originalTag = $this->create(Tag::class);
        $tag = $this->create(Tag::class);
        $tag->name = $originalTag->name;
        $data = $this->createTagDataStructure($tag);

        $this->putJson($this->getSpecificEndPoint($tag->id), $data)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_CONFLICT)))
            ->assertStatus(Response::HTTP_CONFLICT);

        $data = $this->createTagDataStructure($originalTag);
        $this->assertDatabaseHas('tags', $data);

        $count = Tag::where('slug', $originalTag->slug)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function delete_tag()
    {
        $tag = $this->create(Tag::class);

        $this->deleteJson($this->getSpecificEndPoint($tag->id))
            ->assertStatus(Response::HTTP_ACCEPTED)
            ->assertSee($this->responseBuilder->message(Response::HTTP_ACCEPTED, AllowedActions::DESTROY));

        $data = $this->createTagDataStructure($tag);
        $this->assertDatabaseMissing('tags', $data);
    }

    /** @test */
    public function delete_non_existent_tag()
    {
        $this->deleteJson($this->getSpecificEndPoint($this->nonExistentId))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertSee(json_encode($this->responseBuilder->message(Response::HTTP_NOT_FOUND)));
    }

    private function emptyTagDataStructure(): array
    {
        return [
            'name' => '',
        ];
    }

    private function createTagDataStructure(Tag $tag): array
    {
        $data = $this->emptyTagDataStructure();
        $data['name'] = $tag->name;

        return $data;
    }

}
