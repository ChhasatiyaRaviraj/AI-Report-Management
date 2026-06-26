<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books'];

        $productPrefixes = [
            'Electronics' => ['Smart', 'Pro', 'Ultra', 'Digital', 'Wireless'],
            'Clothing' => ['Classic', 'Modern', 'Premium', 'Casual', 'Slim'],
            'Home & Garden' => ['Eco', 'Deluxe', 'Compact', 'Natural', 'Garden'],
            'Sports' => ['Active', 'Power', 'Flex', 'Elite', 'Performance'],
            'Books' => ['Essential', 'Complete', 'Advanced', 'Beginner', 'Master'],
        ];

        $productSuffixes = [
            'Electronics' => ['Speaker', 'Headphones', 'Charger', 'Monitor', 'Keyboard', 'Mouse', 'Webcam', 'Hub'],
            'Clothing' => ['T-Shirt', 'Jacket', 'Pants', 'Sneakers', 'Hoodie', 'Cap', 'Belt', 'Socks'],
            'Home & Garden' => ['Planter', 'Lamp', 'Organizer', 'Cushion', 'Toolset', 'Vase', 'Mat', 'Shelf'],
            'Sports' => ['Yoga Mat', 'Dumbbells', 'Resistance Band', 'Water Bottle', 'Gloves', 'Shoes', 'Bag', 'Watch'],
            'Books' => ['Guide', 'Handbook', 'Workbook', 'Manual', 'Encyclopedia', 'Journal', 'Atlas', 'Almanac'],
        ];

        $category = $this->faker->randomElement($categories);
        $prefix = $this->faker->randomElement($productPrefixes[$category]);
        $suffix = $this->faker->randomElement($productSuffixes[$category]);
        $name = "$prefix $suffix";

        return [
            'name' => $name,
            'sku' => strtoupper(substr($category, 0, 3)) . '-' . $this->faker->unique()->numerify('####'),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'category' => $category,
        ];
    }
}
