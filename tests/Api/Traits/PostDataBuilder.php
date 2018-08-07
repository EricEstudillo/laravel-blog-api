<?php

namespace Tests\Api\Traits;

use App\Post;

trait PostDataBuilder
{
    private function emptyPostDataStructure(): array
    {
        return [
            'title' => '',
            'body' => '',
            'publish_at' => '',
            'tag_id' => [],
        ];
    }

    private function createPostDataStructure(Post $post, $removeFields = [], $relations = []): array
    {
        $data = $this->emptyPostDataStructure();
        $data['title'] = $post->title;
        $data['body'] = $post->body;
        $data['publish_at'] = $post->publish_at;

        foreach($relations as $key => $values){
                $data[$key] = $values;
        }

        foreach ($removeFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}