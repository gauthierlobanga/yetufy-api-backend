<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function contactIndex()
    {
        return response()->json($this->getPageProps());
    }

    public function contactCreate()
    {
        return response()->json($this->getPageProps());
    }

    public function contactStore(StoreContactRequest $request)
    {
        $validated = $request->validated();

        Contact::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'] ?? null,
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'categorie' => $validated['categorie'],
            'sujet' => $validated['sujet'],
            'message' => $validated['message'],
            'status' => Contact::STATUS_EN_ATTENTE,
            'priorite' => Contact::inferPriority($validated['categorie'], $validated['message']),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            'metadata' => array_filter([
                'source' => 'contact_page',
                'url' => $request->fullUrl(),
                'locale' => app()->getLocale(),
            ]),
        ]);

        return response()->json(['success' => 'Votre message a bien ete envoye. Notre equipe vous recontactera tres vite.']);
    }

    private function getPageProps(): array
    {
        return [
            'categories' => Contact::getCategories(),
            'contactMeta' => [
                'appName' => config('app.name'),
                'email' => config('mail.from.address', 'contact@plateform-ecommerces.test'),
                'phone' => null,
                'responseTime' => '< 24h ouvrees',
                'availability' => 'Traitement prioritaire du lundi au samedi',
                'supportHours' => 'Support commercial et technique pendant les heures ouvrables',
                'location' => 'Accompagnement a distance et sur rendez-vous',
            ],
        ];
    }
}
