<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use App\Models\VendorRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorRequestFactory extends Factory
{
    protected $model = VendorRequest::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'shop_name' => $this->faker->company,
            'shop_slug' => $this->faker->unique()->slug,
            'shop_description' => $this->faker->paragraph,
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber,
            'status' => VendorRequest::STATUS_PENDING,
        ];
    }
}
