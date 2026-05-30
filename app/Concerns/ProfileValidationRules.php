<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(int|string|null $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    // protected function emailRules(int|string|null $userId = null): array
    // {
    //     // Déterminer la connexion à utiliser pour la validation
    //     $connection = null;
    //     if (function_exists('tenancy') && tenancy()->initialized) {
    //         $connection = 'tenant';
    //     }

    //     $uniqueRule = $userId === null
    //         ? Rule::unique(User::class)
    //         : Rule::unique(User::class)->ignore($userId);

    //     // Si une connexion spécifique est définie, l'utiliser pour la validation
    //     if ($connection) {
    //         $uniqueRule->connection($connection);
    //     }

    //     return [
    //         'required',
    //         'string',
    //         'email',
    //         'max:255',
    //         $uniqueRule,
    //     ];
    // }

    protected function emailRules(int|string|null $userId = null): array
    {
        $uniqueRule = $userId === null
            ? Rule::unique(User::class)
            : Rule::unique(User::class)->ignore($userId);

        // Supprimez complètement le bloc avec ->connection()
        // Le modèle User utilise déjà la bonne connexion (centrale ou tenant)

        return [
            'required',
            'string',
            'email',
            'max:255',
            $uniqueRule,
        ];
    }
}
