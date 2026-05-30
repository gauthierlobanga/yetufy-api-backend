<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VendorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // Plan
            'plan_id' => ['required', 'exists:plans,id'],

            // Informations de base
            'shop_name' => [
                'required', 'string', 'min:3', 'max:100',
                Rule::unique('tenants', 'raison_sociale'),
            ],
            'shop_slug' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tenants', 'slug'),
                Rule::unique('vendor_requests', 'shop_slug'),
            ],
            'shop_description' => ['nullable', 'string', 'max:500'],

            // Contact
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            // Validation légère côté serveur: certains vendeurs saisissent le numéro local sans indicatif.
            'phone_full' => ['nullable', 'string', 'max:40'],
            // 'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],

            // Localisation & préférences
            'currency' => ['nullable', 'string', 'max:3'],
            'language' => ['nullable', 'string', 'max:5'],

            // Logo
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],

            // Réseaux sociaux
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],

            // Documents légaux
            'documents' => ['nullable', 'array'],
            'documents.*.type_document_id' => [
                'required', 'uuid', 'exists:type_documents_legaux,id',
            ],
            'documents.*.numero_document' => ['nullable', 'string', 'max:100'],
            'documents.*.date_delivrance' => ['nullable', 'date', 'before_or_equal:today'],
            'documents.*.date_expiration' => ['nullable', 'date', 'after:documents.*.date_delivrance'],
            'documents.*.lieu_delivrance' => ['nullable', 'string', 'max:255'],
            'documents.*.autorite_delivrance' => ['nullable', 'string', 'max:255'],

            // Conditions
            'accept_terms' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required' => 'Le nom de la boutique est requis.',
            'shop_name.unique' => 'Ce nom de boutique est déjà utilisé.',
            'shop_slug.required' => 'Le sous-domaine est requis.',
            'shop_slug.regex' => 'Le sous-domaine ne peut contenir que des lettres minuscules, chiffres et tirets.',
            'shop_slug.unique' => 'Ce sous-domaine est déjà utilisé.',
            'contact_email.required' => "L'email de contact est requis.",
            'accept_terms.accepted' => 'Vous devez accepter les conditions générales.',
            'documents.*.type_document_id.required' => 'Le type de document est requis.',
            'documents.*.type_document_id.exists' => "Le type de document sélectionné n'existe pas.",
            'documents.*.date_expiration.after' => "La date d'expiration doit être postérieure à la date de délivrance.",
            'documents.*.date_delivrance.before_or_equal' => 'La date de délivrance ne peut pas être dans le futur.',
            'phone_full.phone' => 'Le numéro de téléphone n\'est pas valide pour le pays sélectionné.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Construire le numéro complet avec le code pays
        $phoneCode = $this->phone_code ?? '+243';
        $phoneNumber = $this->contact_phone;

        $this->merge([
            'shop_slug' => strtolower($this->shop_slug),
            'phone_full' => $phoneNumber ? $phoneCode.$phoneNumber : null,
        ]);
    }
}
