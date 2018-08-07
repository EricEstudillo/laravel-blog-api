<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use function is_null;

class Post extends Model
{
    protected $fillable = ['title', 'body', 'user_id', 'slug', 'publish_at'];
    protected $with = ['tags', 'images'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($post) {
            if (!is_null($post->publish_at)) {
                $date = Carbon::createFromFormat(DateTime::ATOM, $post->publish_at);
                $post->publish_at = $date->tz('UTC')->toDateTimeString();
            }

            $slug = str_slug($post->title);
            $count = static::raw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            $post->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
