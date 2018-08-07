<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Helpers\ImageResponseBuilder;
use App\Image;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ImageController extends ApiController
{
    protected const VALIDATION_RULES = [
        'name' => 'required|max:100',
        'path' => 'required',
        'post_id' => 'required|exists:posts,id',
    ];

    public function index()
    {
        $images = Image::all();

        return $this->responseBuilder->createListResponse(
            Response::HTTP_OK,
            [],
            ['images' => $images->toArray()]
        );
    }

    public function show(string $id = '')
    {
        $image = Image::find($id);
        if (null == $image) {
            return $this->responseBuilder->createShowResponse(Response::HTTP_NOT_FOUND);
        }


        return $this->responseBuilder->createShowResponse(
            Response::HTTP_OK,
            [],
            ['image' => $image->toArray()]
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES);
        if ($validator->fails()) {

            return $this->responseBuilder->createStoreResponse(
                Response::HTTP_BAD_REQUEST, $validator->errors()->toArray()
            );
        }

        $image = new Image([
            'name' => $request->input('name'),
            'path' => $request->input('path'),
        ]);
        $post = Post::find($request->input('post_id'));
        $post->images()->save($image);

        return $this->responseBuilder->createStoreResponse(
            Response::HTTP_CREATED,
            [],
            ['image' => $image->toArray()]
        );
    }

    public function update(Request $request, string $id = '')
    {
        $image = Image::find($id);
        if (null == $image) {
            return $this->responseBuilder->createUpdateResponse(Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), self::VALIDATION_RULES);
        if ($validator->fails()) {

            return $this->responseBuilder->createUpdateResponse(
                Response::HTTP_BAD_REQUEST,
                $validator->errors()->toArray()
            );
        }

        $image->name = $request->input('name');
        $image->path = $request->input('path');
        $post = Post::find($request->input('post_id'));
        $post->images()->save($image);


        return $this->responseBuilder->createUpdateResponse(
            Response::HTTP_CREATED,
            [],
            ['image' => $image->toArray()]
        );
    }

    public function destroy(string $id = '')
    {
        $image = Image::find($id);
        if (null == $image) {
            return $this->responseBuilder->createDestroyResponse(
                Response::HTTP_NOT_FOUND
            );
        }

        $image->delete();

        return $this->responseBuilder->createDestroyResponse(
            Response::HTTP_ACCEPTED
        );
    }

    protected function instantiateResponseBuilder()
    {
        return new ImageResponseBuilder();
    }
}
