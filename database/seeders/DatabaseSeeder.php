<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create chauffeur user
        User::create([
            'name' => 'Chauffeur User',
            'email' => 'chauffeur@example.com',
            'password' => bcrypt('password'),
            'role' => 'chauffeur',
        ]);

        // Create some vehicles (only if they don't exist)
        $vehicle1 = \App\Models\Vehicle::firstOrCreate(
            ['registration_number' => 'ABC-123'],
            [
                'model' => 'Toyota Camry',
                'year' => 2022,
            ]
        );

        $vehicle2 = \App\Models\Vehicle::firstOrCreate(
            ['registration_number' => 'XYZ-789'],
            [
                'model' => 'Honda Civic',
                'year' => 2021,
            ]
        );

        // Assign vehicle to chauffeur
        $chauffeur = User::where('email', 'chauffeur@example.com')->first();
        $chauffeur->update(['vehicle_id' => $vehicle1->id]);
    }
}
