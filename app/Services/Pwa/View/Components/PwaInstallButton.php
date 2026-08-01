<?php

namespace App\Services\Pwa\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PwaInstallButton extends Component
{
    public function __construct(
        public ?string $label = null,
        public string $class = '',
        public string $id = '',
    ) {
    }

    public function render(): View
    {
        return view('pwa::components.install-button', [
            'label' => $this->label ?: (string) config('pwa.install.label', 'Install app'),
            'class' => $this->class,
            'id' => $this->id,
        ]);
    }
}