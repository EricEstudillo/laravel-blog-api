<?php

namespace App\Rules;

use App\Tag;
use Illuminate\Contracts\Validation\Rule;

class ValidTags implements Rule
{
    public function passes($attribute, $tagIds)
    {
        $tagIds = is_array($tagIds) ? $tagIds : [$tagIds];

        return count($tagIds) == Tag::whereIn('id', $tagIds)->count();
    }

    public function message()
    {
        return "One or more tags don't exist.";
    }
}
