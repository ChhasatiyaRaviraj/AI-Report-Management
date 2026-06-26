<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ReturnRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnRecord>
 */
class ReturnRecordFactory extends Factory
{
    protected $model = ReturnRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reasons = [
            'Defective product',
            'Wrong item received',
            'Not as described',
            'Changed mind',
            'Arrived too late',
            'Better price found elsewhere',
        ];

        // Pick a completed order that doesn't already have a return
        $order = Order::where('status', 'completed')
            ->whereDoesntHave('returnRecord')
            ->inRandomOrder()
            ->first();

        // If no valid order exists, create one
        if (!$order) {
            $order = Order::factory()->create();
        }

        // Refund is between 50% and 100% of order total
        $refundPercentage = $this->faker->randomFloat(2, 0.5, 1.0);
        $refundAmount = round($order->total_amount * $refundPercentage, 2);

        return [
            'order_id' => $order->id,
            'reason' => $this->faker->randomElement($reasons),
            'refund_amount' => $refundAmount,
            'return_date' => $this->faker->dateTimeBetween($order->order_date, 'now')->format('Y-m-d'),
        ];
    }
}
