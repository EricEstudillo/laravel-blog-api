<?php

use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\User::where('email', 'testadmin@minblog.test')->first();
        factory(\App\Post::class,30)->create(['user_id' => $user->id]);
    }
}
