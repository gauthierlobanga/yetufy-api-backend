<?php

namespace App\Contracts;

interface Addressable
{
    /**
     * Obtenir les adresses de l'entité
     */
    public function adresses();

    /**
     * Obtenir l'adresse par défaut pour un type donné
     */
    public function getAdresseParDefaut(?string $type = null);

    /**
     * Définir l'adresse par défaut pour un type donné
     */
    public function setAdresseParDefaut(string $adresseId, ?string $type = null);

    /**
     * Obtenir le label de l'entité (pour affichage)
     */
    public function getAddressableLabel(): string;
}
