<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AvailableAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addresses = [
            [
                'name_ar' => 'مكة',
                'name_en' => 'Mecca',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'المدينة',
                'name_en' => 'Medina',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'الرياض',
                'name_en' => 'Riyadh',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('available_addresses')->insert($addresses);
    }
}
