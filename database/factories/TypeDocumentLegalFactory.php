<?php

namespace Database\Factories;

use App\Models\TypeDocumentLegal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TypeDocumentLegalFactory extends Factory
{
    protected $model = TypeDocumentLegal::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'code' => $this->faker->unique()->word,
            'nom' => $this->faker->sentence,
            'est_obligatoire' => $this->faker->boolean,
            'ordre' => $this->faker->numberBetween(0, 100),
        ];
    }
}
