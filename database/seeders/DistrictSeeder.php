<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * All 64 districts of Bangladesh. Each gets a "<District> Sadar" thana as a
     * safe baseline; admins can add the remaining upazilas per district later
     * via the Districts & Thanas admin screen.
     */
    public function run(): void
    {
        $districts = [
            // Barishal division
            'Barguna', 'Barishal', 'Bhola', 'Jhalokati', 'Patuakhali', 'Pirojpur',
            // Chattogram division
            'Bandarban', 'Brahmanbaria', 'Chandpur', 'Chattogram', 'Cumilla',
            "Cox's Bazar", 'Feni', 'Khagrachari', 'Lakshmipur', 'Noakhali', 'Rangamati',
            // Dhaka division
            'Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj', 'Madaripur',
            'Manikganj', 'Munshiganj', 'Narayanganj', 'Narsingdi', 'Rajbari',
            'Shariatpur', 'Tangail',
            // Khulna division
            'Bagerhat', 'Chuadanga', 'Jashore', 'Jhenaidah', 'Khulna', 'Kushtia',
            'Magura', 'Meherpur', 'Narail', 'Satkhira',
            // Mymensingh division
            'Jamalpur', 'Mymensingh', 'Netrokona', 'Sherpur',
            // Rajshahi division
            'Bogura', 'Chapainawabganj', 'Joypurhat', 'Naogaon', 'Natore', 'Pabna',
            'Rajshahi', 'Sirajganj',
            // Rangpur division
            'Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari',
            'Panchagarh', 'Rangpur', 'Thakurgaon',
            // Sylhet division
            'Habiganj', 'Moulvibazar', 'Sunamganj', 'Sylhet',
        ];

        foreach ($districts as $index => $name) {
            $district = District::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $index, 'is_active' => true]
            );

            $district->thanas()->firstOrCreate(['name' => "{$name} Sadar"]);
        }
    }
}
