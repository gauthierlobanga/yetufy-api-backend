<?php

use Filament\Support\Icons\Heroicon;

return [
    'page' => [
        'slug' => 'alert-box',   // URL slug for the settings page
        'cluster' => null,          // Optional cluster class
    ],

    'toolbar_buttons' => [
        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
        ['undo', 'redo'],
    ],

    'default_icons' => [
        'info' => Heroicon::OutlinedInformationCircle,
        'tip' => Heroicon::OutlinedLightBulb,
        'success' => Heroicon::OutlinedCheckCircle,
        'warning' => Heroicon::OutlinedExclamationTriangle,
        'danger' => Heroicon::OutlinedFire,
        'none' => null,
    ],

    'default_colors' => [
        'info' => 'sky',
        'tip' => 'purple',
        'success' => 'green',
        'warning' => 'yellow',
        'danger' => 'red',
        'none' => null,
    ],

    // Add your own Filament render hooks here
    // See: https://filamentphp.com/docs/5.x/advanced/render-hooks
    'custom_hooks' => [
        // 'panels::my-custom-hook',
    ],
];
