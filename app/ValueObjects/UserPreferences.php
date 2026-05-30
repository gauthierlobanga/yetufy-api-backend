<?php

namespace App\ValueObjects;

use JsonSerializable;

class UserPreferences implements JsonSerializable
{
    private array $preferences;

    // Constantes pour les clés de préférences
    public const KEY_NOTIFICATIONS = 'notifications';

    public const KEY_THEME = 'theme';

    public const KEY_LANGUAGE = 'language';

    public const KEY_TIMEZONE = 'timezone';

    public const KEY_DATE_FORMAT = 'date_format';

    public const KEY_CURRENCY = 'currency';

    public const KEY_PAGE_SIZE = 'page_size';

    public const KEY_DASHBOARD_WIDGETS = 'dashboard_widgets';

    public const KEY_EMAIL_FREQUENCY = 'email_frequency';

    public const KEY_MARKETING_CONSENT = 'marketing_consent';

    // Valeurs par défaut
    private const DEFAULTS = [
        self::KEY_NOTIFICATIONS => true,
        self::KEY_THEME => 'light',
        self::KEY_LANGUAGE => 'fr',
        self::KEY_TIMEZONE => 'Europe/Paris',
        self::KEY_DATE_FORMAT => 'd/m/Y',
        self::KEY_CURRENCY => 'EUR',
        self::KEY_PAGE_SIZE => 25,
        self::KEY_DASHBOARD_WIDGETS => [],
        self::KEY_EMAIL_FREQUENCY => 'daily',
        self::KEY_MARKETING_CONSENT => false,
    ];

    // Thèmes disponibles
    public const AVAILABLE_THEMES = ['light', 'dark', 'system'];

    // Langues disponibles
    public const AVAILABLE_LANGUAGES = ['fr', 'en', 'es', 'de', 'it'];

    // Formats de date disponibles
    public const AVAILABLE_DATE_FORMATS = [
        'd/m/Y' => 'DD/MM/YYYY',
        'm/d/Y' => 'MM/DD/YYYY',
        'Y-m-d' => 'YYYY-MM-DD',
        'd M Y' => 'DD Mon YYYY',
    ];

    // Fuseaux horaires courants
    public const AVAILABLE_TIMEZONES = [
        'Europe/Paris' => 'Paris',
        'Europe/London' => 'Londres',
        'America/New_York' => 'New York',
        'Asia/Tokyo' => 'Tokyo',
        'UTC' => 'UTC',
    ];

    // Fréquences d'email
    public const AVAILABLE_EMAIL_FREQUENCIES = [
        'realtime' => 'Temps réel',
        'daily' => 'Quotidien',
        'weekly' => 'Hebdomadaire',
        'never' => 'Jamais',
    ];

    public function __construct(?array $preferences = null)
    {
        $this->preferences = $preferences ?? self::DEFAULTS;
        $this->validate();
    }

    /**
     * Valide les préférences
     */
    private function validate(): void
    {
        // Valider le thème
        if (
            isset($this->preferences[self::KEY_THEME]) &&
            ! in_array($this->preferences[self::KEY_THEME], self::AVAILABLE_THEMES)
        ) {
            $this->preferences[self::KEY_THEME] = self::DEFAULTS[self::KEY_THEME];
        }

        // Valider la langue
        if (
            isset($this->preferences[self::KEY_LANGUAGE]) &&
            ! in_array($this->preferences[self::KEY_LANGUAGE], self::AVAILABLE_LANGUAGES)
        ) {
            $this->preferences[self::KEY_LANGUAGE] = self::DEFAULTS[self::KEY_LANGUAGE];
        }

        // Valider le fuseau horaire
        if (
            isset($this->preferences[self::KEY_TIMEZONE]) &&
            ! in_array($this->preferences[self::KEY_TIMEZONE], array_keys(self::AVAILABLE_TIMEZONES))
        ) {
            $this->preferences[self::KEY_TIMEZONE] = self::DEFAULTS[self::KEY_TIMEZONE];
        }

        // Valider le format de date
        if (
            isset($this->preferences[self::KEY_DATE_FORMAT]) &&
            ! in_array($this->preferences[self::KEY_DATE_FORMAT], array_keys(self::AVAILABLE_DATE_FORMATS))
        ) {
            $this->preferences[self::KEY_DATE_FORMAT] = self::DEFAULTS[self::KEY_DATE_FORMAT];
        }

        // Valider la fréquence d'email
        if (
            isset($this->preferences[self::KEY_EMAIL_FREQUENCY]) &&
            ! in_array($this->preferences[self::KEY_EMAIL_FREQUENCY], array_keys(self::AVAILABLE_EMAIL_FREQUENCIES))
        ) {
            $this->preferences[self::KEY_EMAIL_FREQUENCY] = self::DEFAULTS[self::KEY_EMAIL_FREQUENCY];
        }

        // Valider les notifications
        if (isset($this->preferences[self::KEY_NOTIFICATIONS])) {
            $this->preferences[self::KEY_NOTIFICATIONS] = (bool) $this->preferences[self::KEY_NOTIFICATIONS];
        }

        // Valider le consentement marketing
        if (isset($this->preferences[self::KEY_MARKETING_CONSENT])) {
            $this->preferences[self::KEY_MARKETING_CONSENT] = (bool) $this->preferences[self::KEY_MARKETING_CONSENT];
        }

        // Valider la taille de page
        if (isset($this->preferences[self::KEY_PAGE_SIZE])) {
            $size = (int) $this->preferences[self::KEY_PAGE_SIZE];
            $this->preferences[self::KEY_PAGE_SIZE] = in_array($size, [10, 25, 50, 100]) ? $size : self::DEFAULTS[self::KEY_PAGE_SIZE];
        }
    }

    /**
     * Récupère une préférence
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->preferences[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Définit une préférence
     */
    public function set(string $key, mixed $value): self
    {
        $this->preferences[$key] = $value;
        $this->validate();

        return $this;
    }

    /**
     * Vérifie si une préférence existe
     */
    public function has(string $key): bool
    {
        return isset($this->preferences[$key]);
    }

    /**
     * Supprime une préférence
     */
    public function remove(string $key): self
    {
        unset($this->preferences[$key]);

        return $this;
    }

    /**
     * Récupère toutes les préférences
     */
    public function all(): array
    {
        return $this->preferences;
    }

    /**
     * Réinitialise les préférences aux valeurs par défaut
     */
    public function reset(): self
    {
        $this->preferences = self::DEFAULTS;

        return $this;
    }

    /**
     * Fusionne avec d'autres préférences
     */
    public function merge(array $preferences): self
    {
        $this->preferences = array_merge($this->preferences, $preferences);
        $this->validate();

        return $this;
    }

    /**
     * Convertit en tableau pour JSON
     */
    public function jsonSerialize(): array
    {
        return $this->preferences;
    }

    /**
     * Convertit en tableau
     */
    public function toArray(): array
    {
        return $this->preferences;
    }

    /**
     * Crée une instance à partir d'un tableau
     */
    public static function fromArray(?array $data): self
    {
        return new self($data);
    }

    // Méthodes d'accès rapide pour les préférences courantes

    public function getTheme(): string
    {
        return $this->get(self::KEY_THEME);
    }

    public function getLanguage(): string
    {
        return $this->get(self::KEY_LANGUAGE);
    }

    public function getTimezone(): string
    {
        return $this->get(self::KEY_TIMEZONE);
    }

    public function getDateFormat(): string
    {
        return $this->get(self::KEY_DATE_FORMAT);
    }

    public function getCurrency(): string
    {
        return $this->get(self::KEY_CURRENCY);
    }

    public function getPageSize(): int
    {
        return (int) $this->get(self::KEY_PAGE_SIZE);
    }

    public function wantsNotifications(): bool
    {
        return (bool) $this->get(self::KEY_NOTIFICATIONS);
    }

    public function wantsMarketing(): bool
    {
        return (bool) $this->get(self::KEY_MARKETING_CONSENT);
    }

    public function getEmailFrequency(): string
    {
        return $this->get(self::KEY_EMAIL_FREQUENCY);
    }
}
