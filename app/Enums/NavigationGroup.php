<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum NavigationGroup implements HasIcon, HasLabel
{
    case Shop;

    case Blog;

    case Settings;

    case Profile;

    case Shield;

    case Contact;

    case Notification;

    case About;

    case Help;

    case Terms;

    case PrivacyPolicy;

    case SupportTechnique;

    case SupportClient;

    case Share;

    case Ecommerce;

    case World;
    case Organisation;

    public function getLabel(): string
    {
        return match ($this) {
            self::Shop => __('Market'),
            self::Blog => __('Blog'),
            self::Settings => __('Paramètres'),
            self::Profile => __('Compte'),
            self::Shield => __('Bouclier'),
            self::Contact => __('Contact'),
            self::Notification => __('Notification'),
            self::About => __('A propos'),
            self::Help => __('Aide'),
            self::Terms => __('Termes'),
            self::PrivacyPolicy => __('Politique'),
            self::SupportTechnique => __('Support technique'),
            self::SupportClient => __('Support Client'),
            self::Share => __('Partager'),
            self::World => __('Monde'),
            self::Organisation => __('Entreprise'),
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Shop => Heroicon::OutlinedShoppingCart,
            self::Blog => Heroicon::OutlinedNewspaper,
            self::Settings => Heroicon::OutlinedCog6Tooth,
            self::Profile => Heroicon::OutlinedUsers,
            self::Shield => Heroicon::OutlinedShieldCheck,
            self::Contact => Heroicon::OutlinedChatBubbleLeftRight,
            self::Notification => Heroicon::OutlinedBellAlert,
            self::About => Heroicon::OutlinedInformationCircle,
            self::Help => Heroicon::OutlinedQuestionMarkCircle,
            self::Terms => Heroicon::OutlinedDocumentText,
            self::PrivacyPolicy => Heroicon::OutlinedShieldCheck,
            self::SupportTechnique => Heroicon::OutlinedLifebuoy,
            self::SupportClient => Heroicon::OutlinedChatBubbleOvalLeftEllipsis,
            self::Share => Heroicon::OutlinedShare,
            self::World => Heroicon::OutlinedGlobeAlt,
            self::Organisation => Heroicon::OutlinedBuildingStorefront,
        };
    }
}
