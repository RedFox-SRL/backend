<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        $testUsers = [
            ['name' => 'Test', 'last_name' => 'User1', 'email' => 'redfox.es1@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User2', 'email' => 'redfox.es2@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User3', 'email' => 'redfox.es3@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User4', 'email' => 'redfox.es4@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User5', 'email' => 'redfox.es5@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User6', 'email' => 'redfox.es6@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User7', 'email' => 'redfox.es7@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User8', 'email' => 'redfox.es8@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User9', 'email' => 'redfox.es9@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User10', 'email' => 'redfox.es10@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User11', 'email' => 'redfox.es11@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User12', 'email' => 'redfox.do11@fcyt.umss.edu.bo', 'role' => 'teacher'],
        ];

        foreach ($testUsers as $testUser) {
            $user = User::create($testUser);

            if ($user->role === 'student') {
                Student::create(['user_id' => $user->id]);
            } elseif ($user->role === 'teacher') {
                Teacher::create(['user_id' => $user->id]);
            }
        }
    }
}
