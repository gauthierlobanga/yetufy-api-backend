<?php

namespace App\Services;

class TenantThemeService
{
    public function generatePalette(string $accentColor, string $neutralColor): array
    {
        $accentHsl = $this->parseHsl($accentColor);
        $neutralHsl = $this->parseHsl($neutralColor);

        $primary = $accentColor;
        $primaryForeground = '0 0% 100%';

        $secondary = $this->lightenHsl($neutralHsl, 0.9);
        $secondaryForeground = $neutralColor;

        $muted = $this->lightenHsl($neutralHsl, 0.8);
        $mutedForeground = $neutralColor;

        $accent = $this->lightenHsl($accentHsl, 0.85);
        $accentForeground = $accentColor;

        $destructive = '0 84% 60%';
        $destructiveForeground = '0 0% 100%';

        $border = $neutralColor;
        $input = $neutralColor;
        $ring = $accentColor;

        $background = '0 0% 100%';
        $foreground = $neutralColor;

        $card = '0 0% 100%';
        $cardForeground = $neutralColor;

        $popover = '0 0% 100%';
        $popoverForeground = $neutralColor;

        $darkBackground = $neutralColor;
        $darkForeground = '210 40% 98%';

        return [
            '--background' => $background,
            '--foreground' => $foreground,
            '--card' => $card,
            '--card-foreground' => $cardForeground,
            '--popover' => $popover,
            '--popover-foreground' => $popoverForeground,
            '--primary' => $primary,
            '--primary-foreground' => $primaryForeground,
            '--secondary' => $secondary,
            '--secondary-foreground' => $secondaryForeground,
            '--muted' => $muted,
            '--muted-foreground' => $mutedForeground,
            '--accent' => $accent,
            '--accent-foreground' => $accentForeground,
            '--destructive' => $destructive,
            '--destructive-foreground' => $destructiveForeground,
            '--border' => $border,
            '--input' => $input,
            '--ring' => $ring,
        ];
    }

    private function parseHsl(string $hsl): array
    {
        preg_match('/([\d.]+)\s+([\d.]+)%\s+([\d.]+)%/', $hsl, $matches);

        return ['h' => (float) $matches[1], 's' => (float) $matches[2], 'l' => (float) $matches[3]];
    }

    private function lightenHsl(array $hsl, float $factor): string
    {
        $l = min(100, $hsl['l'] + (100 - $hsl['l']) * $factor);

        return "{$hsl['h']} {$hsl['s']}% {$l}%";
    }
}
