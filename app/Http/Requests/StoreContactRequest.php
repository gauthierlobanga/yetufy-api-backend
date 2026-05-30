<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nom' => trim((string) $this->input('nom')),
            'prenom' => trim((string) $this->input('prenom')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'telephone' => trim((string) $this->input('telephone')),
            'sujet' => trim((string) $this->input('sujet')),
            'message' => trim((string) $this->input('message')),
            'categorie' => trim((string) $this->input('categorie')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'min:2', 'max:120'],
            'prenom' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'categorie' => ['required', 'string', Rule::in(array_keys(Contact::getCategories()))],
            'sujet' => ['required', 'string', 'min:4', 'max:160'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.min' => 'Le nom doit contenir au moins 2 caracteres.',
            'prenom.max' => 'Le prenom est trop long.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'telephone.max' => 'Le numero de telephone est trop long.',
            'categorie.required' => 'Veuillez choisir une categorie.',
            'categorie.in' => 'La categorie selectionnee est invalide.',
            'sujet.required' => 'Le sujet est obligatoire.',
            'sujet.min' => 'Le sujet doit contenir au moins 4 caracteres.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 20 caracteres.',
            'message.max' => 'Le message est trop long.',
        ];
    }
}
