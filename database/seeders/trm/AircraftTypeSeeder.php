<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AircraftTypeSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftTypes = [
            // Narrow Body
            ['code' => 'A320', 'name' => 'Airbus A320', 'category' => 'narrow_body'],
            ['code' => 'A321', 'name' => 'Airbus A321', 'category' => 'narrow_body'],
            ['code' => 'A319', 'name' => 'Airbus A319', 'category' => 'narrow_body'],
            ['code' => 'A320N', 'name' => 'Airbus A320neo', 'category' => 'narrow_body'],
            ['code' => 'A321N', 'name' => 'Airbus A321neo', 'category' => 'narrow_body'],
            ['code' => 'B737', 'name' => 'Boeing 737-800', 'category' => 'narrow_body'],
            ['code' => 'B738', 'name' => 'Boeing 737-800', 'category' => 'narrow_body'],
            ['code' => 'B38M', 'name' => 'Boeing 737 MAX 8', 'category' => 'narrow_body'],
            ['code' => 'B39M', 'name' => 'Boeing 737 MAX 9', 'category' => 'narrow_body'],
            
            // Wide Body
            ['code' => 'A330', 'name' => 'Airbus A330', 'category' => 'wide_body'],
            ['code' => 'A332', 'name' => 'Airbus A330-200', 'category' => 'wide_body'],
            ['code' => 'A333', 'name' => 'Airbus A330-300', 'category' => 'wide_body'],
            ['code' => 'A339', 'name' => 'Airbus A330-900neo', 'category' => 'wide_body'],
            ['code' => 'A350', 'name' => 'Airbus A350', 'category' => 'wide_body'],
            ['code' => 'A359', 'name' => 'Airbus A350-900', 'category' => 'wide_body'],
            ['code' => 'A35K', 'name' => 'Airbus A350-1000', 'category' => 'wide_body'],
            ['code' => 'A380', 'name' => 'Airbus A380', 'category' => 'wide_body'],
            ['code' => 'B777', 'name' => 'Boeing 777', 'category' => 'wide_body'],
            ['code' => 'B772', 'name' => 'Boeing 777-200', 'category' => 'wide_body'],
            ['code' => 'B773', 'name' => 'Boeing 777-300', 'category' => 'wide_body'],
            ['code' => 'B77W', 'name' => 'Boeing 777-300ER', 'category' => 'wide_body'],
            ['code' => 'B787', 'name' => 'Boeing 787 Dreamliner', 'category' => 'wide_body'],
            ['code' => 'B788', 'name' => 'Boeing 787-8', 'category' => 'wide_body'],
            ['code' => 'B789', 'name' => 'Boeing 787-9', 'category' => 'wide_body'],
            ['code' => 'B78X', 'name' => 'Boeing 787-10', 'category' => 'wide_body'],
            
            // Regional
            ['code' => 'E170', 'name' => 'Embraer E170', 'category' => 'regional'],
            ['code' => 'E175', 'name' => 'Embraer E175', 'category' => 'regional'],
            ['code' => 'E190', 'name' => 'Embraer E190', 'category' => 'regional'],
            ['code' => 'E195', 'name' => 'Embraer E195', 'category' => 'regional'],
            ['code' => 'E290', 'name' => 'Embraer E190-E2', 'category' => 'regional'],
            ['code' => 'E295', 'name' => 'Embraer E195-E2', 'category' => 'regional'],
            ['code' => 'CRJ2', 'name' => 'Bombardier CRJ200', 'category' => 'regional'],
            ['code' => 'CRJ7', 'name' => 'Bombardier CRJ700', 'category' => 'regional'],
            ['code' => 'CRJ9', 'name' => 'Bombardier CRJ900', 'category' => 'regional'],
            ['code' => 'AT72', 'name' => 'ATR 72', 'category' => 'regional'],
            ['code' => 'DH8D', 'name' => 'Dash 8 Q400', 'category' => 'regional'],
        ];

        foreach ($aircraftTypes as $type) {
            DB::table('tr2_aircraft_types')->updateOrInsert(
                ['code' => $type['code'], 'client_id' => 1],
                array_merge($type, [
                    'client_id' => 1,
                    'created_at' => now(),
                ])
            );
        }
    }
}
