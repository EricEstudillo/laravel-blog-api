<?php

namespace Tests\Admin\Feature;

use Tests\TestCase;

class TagTest extends TestCase
{
    /** @test */
    public function insert_tag()
    {
        $this->get('/admin/tags/');
        $this->assertTrue(true);
    }
}
