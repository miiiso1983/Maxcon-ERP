<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\LanguageService;

class LanguageSwitcher extends Component
{
    public $currentLanguage;
    public $supportedLanguages;
    public $showFlags;
    public $showNames;
    public $dropdownClass;

    public function __construct(
        bool $showFlags = true,
        bool $showNames = true,
        string $dropdownClass = 'dropdown'
    ) {
        $this->currentLanguage = LanguageService::getCurrentLanguageInfo();
        $this->supportedLanguages = LanguageService::getSupportedLanguages();
        $this->showFlags = $showFlags;
        $this->showNames = $showNames;
        $this->dropdownClass = $dropdownClass;
    }

    public function render()
    {
        return view('components.language-switcher');
    }
}
