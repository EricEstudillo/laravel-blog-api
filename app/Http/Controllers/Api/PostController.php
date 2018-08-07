<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Helpers\PostResponseBuilder;
use App\Post;
use App\Rules\ValidTags;
use function array_merge;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends ApiController
{
    protected const VALIDATION_RULES = [
        'title' => 'required|max:100',
        'body' => 'required',
        'publish_at' => "date_format:" . DateTime::ATOM,
    ];

    public function index()
    {
        $posts = Post::all();
        return $this->responseBuilder->createListResponse(
            Response::HTTP_OK,
            [],
            ['posts' => $posts->toArray()]
        );
    }

    public function show(string $id = '')
    {
        $post = Post::find($id);
        if (null == $post) {
            return $this->responseBuilder->createShowResponse(Response::HTTP_NOT_FOUND);
        }

        return $this->responseBuilder->createShowResponse(
            Response::HTTP_OK,
            [],
            ['post' => $post->toArray()]
        );
    }

    public function store(Request $request)
    {
        $validationRules = $this->addRelationsToValidationRules();
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->responseBuilder->createStoreResponse(
                Response::HTTP_BAD_REQUEST, $validator->errors()->toArray()
            );
        }

        $post = Post::create([
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'user_id' => Auth::id(),
            'publish_at' => $request->input('publish_at'),
        ]);

        $tagIds = $request->input('tag_id');
        $post->tags()->attach($tagIds);
        $post->load(['tags']);

        return $this->responseBuilder->createStoreResponse(
            Response::HTTP_CREATED,
            [],
            ['post' => $post->toArray()]
        );
    }

    public function update(Request $request, string $id = '')
    {
        $post = Post::find($id);
        if (null == $post) {
            return $this->responseBuilder->createUpdateResponse(Response::HTTP_NOT_FOUND);
        }

        $validationRules = self::VALIDATION_RULES;

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return $this->responseBuilder->createUpdateResponse(
                Response::HTTP_BAD_REQUEST,
                $validator->errors()->toArray()
            );
        }

        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->publish_at = $request->input('publish_at');
        $post->save();

        $tagIds = $request->input('tag_id');
        $post->tags()->sync($tagIds);
        $post->refresh();

        return $this->responseBuilder->createUpdateResponse(
            Response::HTTP_CREATED,
            [],
            ['post' => $post->toArray()]
        );
    }

    public function destroy(string $id = '')
    {
        $post = Post::find($id);
        if (null == $post) {

            return $this->responseBuilder->createDestroyResponse(
                Response::HTTP_NOT_FOUND
            );
        }

        $post->delete();

        return $this->responseBuilder->createDestroyResponse(Response::HTTP_ACCEPTED);
    }

    private function addRelationsToValidationRules(): array
    {
        return array_merge(self::VALIDATION_RULES, ['tag_id' => new ValidTags]);
    }

    protected function instantiateResponseBuilder()
    {
        return new PostResponseBuilder();
    }
}
