<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GroupNames extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sqlPath = database_path('sql/group_names.sql');

        $sql = File::get($sqlPath);

        DB::unprepared($sql);

        $this->command->info('Archivo SQL importado correctamente.');
    }
}
