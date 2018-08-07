<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function urldecode;
use function urlencode;

class Image extends Model
{
    protected $fillable = ['name', 'path'];

    public function save(array $options = [])
    {
        $this->path = urlencode(urldecode($this->path));
        return parent::save($options);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
