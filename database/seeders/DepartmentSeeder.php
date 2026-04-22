<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            'IT & Teknologi',
            'Human Resources',
            'Marketing & Sales',
            'Finance & Accounting',
            'Operations',
            'Legal & Compliance'
        ];

        foreach ($depts as $name) {
            Department::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
