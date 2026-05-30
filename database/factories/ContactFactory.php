<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'email' => $this->faker->safeEmail,
            'sujet' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'status' => Contact::STATUS_EN_ATTENTE,
            'categorie' => Contact::CATEGORIE_GENERAL,
        ];
    }
}
