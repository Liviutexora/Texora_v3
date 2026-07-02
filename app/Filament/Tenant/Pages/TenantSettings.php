<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Tenant;
use App\Support\LocalisationOptions;
use App\Support\TenantContext;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Account';

    public static function canAccess(): bool
    {
        return auth()->check() && ! auth()->user()->hasRole('staff');
    }

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.tenant.pages.tenant-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = TenantContext::current();

        $this->form->fill($tenant ? $tenant->toArray() : []);
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Settings');
    }

    public function form(Schema $schema): Schema
    {
        $timezones = collect(\DateTimeZone::listIdentifiers())
            ->mapWithKeys(fn ($tz) => [$tz => $tz])
            ->toArray();

        return $schema
            ->components([
                Section::make(__('Business Profile'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label(__('Business Name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->label(__('Booking URL slug'))
                                ->required()
                                ->alphaDash()
                                ->maxLength(100)
                                ->prefix(url('/') . '/')
                                ->helperText(__('Customers visit this URL to book')),

                            TextInput::make('email')
                                ->label(__('Business Email'))
                                ->email(),

                            TextInput::make('phone')
                                ->label(__('Business Phone')),

                            Textarea::make('address')
                                ->label(__('Address'))
                                ->rows(2)
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make(__('Booking Page Appearance'))
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('logo')
                                ->label(__('Business Logo'))
                                ->image()
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('3:1')
                                ->directory('tenant-logos')
                                ->columnSpan(2),

                            Textarea::make('booking_page_tagline')
                                ->label(__('Tagline'))
                                ->rows(2)
                                ->placeholder(__('e.g. "Premium cuts, zero wait time."'))
                                ->columnSpan(2),

                            ColorPicker::make('booking_page_color')
                                ->label(__('Brand Colour'))
                                ->helperText(__('Used as accent on your booking page')),
                        ]),
                    ]),

                Section::make(__('Regional Settings'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('timezone')
                                ->label(__('Timezone'))
                                ->options($timezones)
                                ->searchable()
                                ->required(),

                            Select::make('currency')
                                ->label(__('Currency'))
                                ->options(fn () => LocalisationOptions::currencies())
                                ->searchable()
                                ->required(),

                            Select::make('locale')
                                ->label(__('Default Language'))
                                ->options(function () {
                                    $all = [
                                        'en' => '🇬🇧 English',
                                        'es' => '🇪🇸 Español',
                                        'de' => '🇩🇪 Deutsch',
                                        'fr' => '🇫🇷 Français',
                                        'ar' => '🇸🇦 العربية (RTL)',
                                        'ru' => '🇷🇺 Русский',
                                        'zh' => '🇨🇳 中文',
                                        'hi' => '🇮🇳 हिन्दी',
                                    ];
                                    return array_intersect_key($all, array_flip(\App\Http\Middleware\SetLocale::enabledLocales()));
                                })
                                ->helperText(__('This language is used as the default for your booking page. Visitors can still override it.'))
                                ->placeholder(__('Use app default (English)')),
                        ]),
                    ]),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $tenant = TenantContext::current();

        // TenantContext is cleared on Livewire AJAX requests (they bypass middleware).
        // Fall back to session impersonation or owner lookup.
        if (! $tenant) {
            $tenantId = session('impersonate_tenant_id')
                ?? Tenant::where('owner_id', auth()->id())->value('id');
            $tenant = $tenantId ? Tenant::find($tenantId) : null;
        }

        if (! $tenant) {
            Notification::make()->title(__('Could not identify your business. Please refresh and try again.'))->danger()->send();
            return;
        }

        // Read directly from the Livewire-synced property.
        // $this->form->getState() can return stale mount-time values in Filament 5
        // because the schema deserialization doesn't always pick up what the user changed.
        $data = $this->data ?? [];

        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? '');

        if (empty($name)) {
            $this->addError('data.name', __('Business name is required.'));
            return;
        }
        if (empty($slug)) {
            $this->addError('data.slug', __('URL slug is required.'));
            return;
        }
        if ($slug !== $tenant->slug && Tenant::where('slug', $slug)->where('id', '!=', $tenant->id)->exists()) {
            $this->addError('data.slug', __('This slug is already taken. Please choose another.'));
            return;
        }

        // Logo: find the FileUpload component and call saveUploadedFiles() directly.
        // This moves any temp-uploaded file to permanent storage (tenant-logos/ on public disk)
        // and returns the final path. Calling $this->form->getState() for the whole form
        // is unreliable in Filament 5 for non-FileUpload fields; targeting the component
        // directly is the correct approach.
        $logo = $tenant->logo;
        try {
            /** @var \Filament\Forms\Components\FileUpload|null $logoComponent */
            $logoComponent = $this->form->getComponent('logo');
            if ($logoComponent) {
                $logoComponent->saveUploadedFiles();
                $componentState = $logoComponent->getState();
                if ($componentState) {
                    $logo = is_array($componentState)
                        ? (array_values(array_filter($componentState))[0] ?? $logo)
                        : $componentState;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('TenantSettings: logo upload processing failed', ['error' => $e->getMessage()]);
        }

        \DB::table('tenants')->where('id', $tenant->id)->update([
            'name'                 => $name,
            'slug'                 => $slug,
            'email'                => trim($data['email'] ?? '') ?: null,
            'phone'                => trim($data['phone'] ?? '') ?: null,
            'address'              => trim($data['address'] ?? '') ?: null,
            'logo'                 => $logo,
            'booking_page_tagline' => trim($data['booking_page_tagline'] ?? '') ?: null,
            'booking_page_color'   => $data['booking_page_color'] ?? null,
            'timezone'             => $data['timezone'] ?: 'UTC',
            'currency'             => $data['currency'] ?: 'INR',
            'locale'               => $data['locale'] ?: null,
            'updated_at'           => now(),
        ]);

        // Propagate currency change to all services so price_formatted stays consistent
        $newCurrency = $data['currency'] ?: 'INR';
        \DB::table('services')->where('tenant_id', $tenant->id)->update(['currency' => $newCurrency]);

        // Refresh TenantContext so in-process requests see updated values
        TenantContext::set(Tenant::find($tenant->id));

        Notification::make()->title(__('Settings saved'))->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }
}
