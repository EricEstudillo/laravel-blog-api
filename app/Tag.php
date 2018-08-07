<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function str_slug;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($tag) {
            $tag->slug = str_slug($tag->name);
            return $isUniqueSlug = static::where("slug", $tag->slug)->doesntExist();
        });
    }


    public function posts()
    {
        return $this->belongsToMany('App\Post');
    }
}
