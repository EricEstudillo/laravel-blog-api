<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'testadmin',
            'email' => 'testadmin@minblog.test',
            'password' => bcrypt('testadmin')
        ]);

        DB::table('oauth_clients')->insert([
            'name' => 'minBlogApiClient',
            'secret' => 'oOOKRVZL8AJgENYpKFqW1Mr6Xn1pKhHqoRO14cvB',
            'redirect'=>'http://localhost',
            'password_client' => 1,
            'personal_access_client'=>0,
            'revoked'=>0,
            'user_id'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
        ]);
    }
}
