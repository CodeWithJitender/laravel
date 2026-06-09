<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HolidayType;

class HolidayTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'National Holiday',
                'code' => 'national',
                'description' => 'Mandatory holidays observed across the country.',
                'status' => 'active',
            ],
            [
                'name' => 'Regional Holiday',
                'code' => 'regional',
                'description' => 'Holidays celebrated in specific states or regions.',
                'status' => 'active',
            ],
            [
                'name' => 'Location Holiday',
                'code' => 'location',
                'description' => 'Holidays limited to a specific physical office location.',
                'status' => 'active',
            ],

            [
                'name' => 'Restricted Holiday',
                'code' => 'restricted',
                'description' => 'Special holidays requiring leave approvals to attend.',
                'status' => 'active',
            ],
            [
                'name' => 'Company Holiday',
                'code' => 'company',
                'description' => 'Corporate holidays defined by the company management.',
                'status' => 'active',
            ],
            [
                'name' => 'Custom Holiday',
                'code' => 'custom',
                'description' => 'Ad-hoc holidays customized by system administrators.',
                'status' => 'active',
            ],
        ];

        foreach ($types as $type) {
            HolidayType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
