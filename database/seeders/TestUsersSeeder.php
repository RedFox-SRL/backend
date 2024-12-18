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
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es1@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es2@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es3@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es4@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es5@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es6@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es7@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es8@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es9@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es10@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es11@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es12@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es13@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es14@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es15@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es16@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es17@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.es18@est.umss.edu', 'role' => 'student'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.do1@fcyt.umss.edu.bo', 'role' => 'teacher'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.do2@fcyt.umss.edu.bo', 'role' => 'teacher'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.do3@fcyt.umss.edu.bo', 'role' => 'teacher'],
            ['name' => 'Test', 'last_name' => 'User', 'email' => 'redfox.do4@fcyt.umss.edu.bo', 'role' => 'teacher'],
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
