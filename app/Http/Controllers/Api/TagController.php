<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Helpers\TagResponseBuilder;
use App\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TagController extends ApiController
{
    protected const VALIDATION_RULES = [
        'name' => 'required',
    ];

    public function index()
    {
        /** @var Collection $tags */
        $tags = Tag::latest()->get();
        return $this->responseBuilder->createListResponse(
            Response::HTTP_OK,
            [],
            ['tags' => $tags->toArray()]
        );
    }

    public function show(string $id)
    {
        $tag = Tag::find($id);
        if (null == $tag) {
            return $this->responseBuilder->createShowResponse(Response::HTTP_NOT_FOUND);
        }

        return $this->responseBuilder->createShowResponse(
            Response::HTTP_OK,
            [],
            ['tag' => $tag->toArray()]
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES);
        if ($validator->fails()) {
            return $this->responseBuilder->createStoreResponse(Response::HTTP_BAD_REQUEST,
                $validator->errors()->toArray());
        }

        $tag = Tag::create([
            'name' => $request->input('name'),
        ]);

        if (false == $tag->wasRecentlyCreated) {
            return $this->responseBuilder->createStoreResponse(Response::HTTP_CONFLICT);
        }

        return $this->responseBuilder->createStoreResponse(
            Response::HTTP_CREATED,
            [],
            ['tag' => $tag->toArray()]
        );
    }

    public function update(Request $request, string $id)
    {
        /** @var Tag $tag */
        $tag = Tag::find($id);
        if (null == $tag) {
            return $this->responseBuilder->createUpdateResponse(Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), self::VALIDATION_RULES);
        if ($validator->fails()) {
            return $this->responseBuilder->createUpdateResponse(
                Response::HTTP_BAD_REQUEST,
                $validator->errors()->toArray()
            );
        }

        $tag->name = $request->input('name');
        $updateResult = $tag->save();

        if (false == $updateResult) {
            return $this->responseBuilder->createUpdateResponse(Response::HTTP_CONFLICT);
        }

        return $this->responseBuilder->createUpdateResponse(
            Response::HTTP_CREATED,
            [],
            ['tag' => $tag->toArray()]
        );
    }

    public function destroy(string $id)
    {
        $tag = Tag::find($id);
        if (null == $tag) {
            return $this->responseBuilder->createDestroyResponse(
                Response::HTTP_NOT_FOUND
            );
        }
        $tag->delete();

        return $this->responseBuilder->createDestroyResponse(Response::HTTP_ACCEPTED);
    }

    protected function instantiateResponseBuilder()
    {
        return new TagResponseBuilder();
    }
}
