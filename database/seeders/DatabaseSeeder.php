<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRecord;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding products...');
        Product::factory(20)->create();

        $this->command->info('Seeding orders...');
        Order::factory(500)->create();

        $this->command->info('Seeding returns...');
        // Create ~50 returns (~10% return rate)
        ReturnRecord::factory(50)->create();

        // Mark the returned orders as 'returned'
        $returnedOrderIds = ReturnRecord::pluck('order_id');
        Order::whereIn('id', $returnedOrderIds)->update(['status' => 'returned']);

        $this->command->info('Seeding complete!');
        $this->command->info("  Products: " . Product::count());
        $this->command->info("  Orders: " . Order::count());
        $this->command->info("  Returns: " . ReturnRecord::count());
    }
}
