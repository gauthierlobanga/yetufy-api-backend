<?php

namespace App\Concerns;

use App\Events\UserLoggedIn;
use App\Events\UserPreferencesApplied;
use App\ValueObjects\UserPreferences;
use Illuminate\Support\Carbon;

trait HasUserPreferences
{
    private ?UserPreferences $preferencesObject = null;

    /**
     * Boot the trait
     */
    protected static function bootHasUserPreferences(): void
    {
        static::updating(function ($model) {
            // Si la date de dernière connexion change, on la met à jour
            if ($model->isDirty('dernier_connexion')) {
                $model->dernier_connexion = now();
            }
        });
    }

    /**
     * Get preferences as object
     */
    public function getPreferencesObject(): UserPreferences
    {
        if (! $this->preferencesObject) {
            $this->preferencesObject = UserPreferences::fromArray(
                json_decode($this->preferences ?? '{}', true)
            );
        }

        return $this->preferencesObject;
    }

    /**
     * Update last login
     */
    public function updateLastLogin(?Carbon $time = null): void
    {
        $this->updateQuietly([
            'dernier_connexion' => $time ?? now(),
        ]);

        // Dispatch event for analytics
        event(new UserLoggedIn($this));
    }

    /**
     * Check if user is online (active in last 5 minutes)
     */
    public function isOnline(): bool
    {
        return $this->dernier_connexion &&
            $this->dernier_connexion->gt(now()->subMinutes(5));
    }

    /**
     * Get last seen in human readable format
     */
    public function getLastSeenAttribute(): ?string
    {
        if (! $this->dernier_connexion) {
            return null;
        }

        return $this->dernier_connexion->diffForHumans();
    }

    /**
     * Get last login time with timezone from preferences
     */
    public function getLastLoginInTimezoneAttribute(): ?string
    {
        if (! $this->dernier_connexion) {
            return null;
        }

        $timezone = $this->getPreferencesObject()->getTimezone();

        return $this->dernier_connexion->setTimezone($timezone)->format(
            $this->getPreferencesObject()->getDateFormat().' H:i'
        );
    }

    /**
     * Scope users online in last X minutes
     */
    public function scopeOnline($query, int $minutes = 5)
    {
        return $query->where('dernier_connexion', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope users not logged in since date
     */
    public function scopeInactiveSince($query, $date)
    {
        return $query->where('dernier_connexion', '<=', $date)
            ->orWhereNull('dernier_connexion');
    }

    /**
     * Get preference
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return $this->getPreferencesObject()->get($key, $default);
    }

    /**
     * Set preference
     */
    public function setPreference(string $key, mixed $value): self
    {
        $preferences = $this->getPreferencesObject();
        $preferences->set($key, $value);

        $this->preferences = json_encode($preferences->toArray());
        $this->save();

        return $this;
    }

    /**
     * Set multiple preferences at once
     */
    public function setPreferences(array $preferences): self
    {
        $prefs = $this->getPreferencesObject();
        $prefs->merge($preferences);

        $this->preferences = json_encode($prefs->toArray());
        $this->save();

        return $this;
    }

    /**
     * Reset preferences to defaults
     */
    public function resetPreferences(): self
    {
        $prefs = new UserPreferences;
        $this->preferences = json_encode($prefs->toArray());
        $this->save();

        return $this;
    }

    /**
     * Check if user has preference
     */
    public function hasPreference(string $key): bool
    {
        return $this->getPreferencesObject()->has($key);
    }

    /**
     * Remove preference
     */
    public function removePreference(string $key): self
    {
        $preferences = $this->getPreferencesObject();
        $preferences->remove($key);

        $this->preferences = json_encode($preferences->toArray());
        $this->save();

        return $this;
    }

    /**
     * Get formatted preference for display
     */
    public function getPreferenceDisplay(string $key): ?string
    {
        $value = $this->getPreference($key);

        return match ($key) {
            UserPreferences::KEY_THEME => ucfirst($value),
            UserPreferences::KEY_LANGUAGE => strtoupper($value),
            UserPreferences::KEY_EMAIL_FREQUENCY => UserPreferences::AVAILABLE_EMAIL_FREQUENCIES[$value] ?? $value,
            UserPreferences::KEY_DATE_FORMAT => UserPreferences::AVAILABLE_DATE_FORMATS[$value] ?? $value,
            UserPreferences::KEY_TIMEZONE => UserPreferences::AVAILABLE_TIMEZONES[$value] ?? $value,
            default => $value,
        };
    }

    /**
     * Apply user preferences to application
     */
    public function applyPreferences(): void
    {
        $prefs = $this->getPreferencesObject();

        // Set application locale
        app()->setLocale($prefs->getLanguage());

        // Set timezone for Carbon
        date_default_timezone_set($prefs->getTimezone());

        // Fire event for other systems
        event(new UserPreferencesApplied($this));
    }
}
