<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\PasswordPolicy;
use Filament\Forms;
use Filament\Pages\Page;
use App\Filament\Concerns\HasPagePermissions;
use App\Helpers\DemoModeHelper;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
class PasswordPolicySettings extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPagePermissions;
    protected static ?string $title = null;
    protected string $view = 'filament.pages.password-policy-settings';
    protected static ?string $slug = 'password-policy-settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static string | \UnitEnum | null $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 31;
    protected static ?string $navigationLabel = 'Password Policy';


    public ?PasswordPolicy $policy = null;
    public array $data = [];

    public function mount(): void
    {
        $this->policy = PasswordPolicy::first() ?? PasswordPolicy::create([
            'name' => 'Default Policy',
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => true,
            'expires_days' => null,
            'history_count' => 5,
            'max_login_attempts' => 5,
            'lockout_duration' => 30,
            'is_default' => true,
        ]);

        $this->form->fill($this->policy->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Password Requirements'))
                    ->schema([
                        TextInput::make('min_length')
                            ->label(__('Minimum Length'))
                            ->numeric()
                            ->default(8)
                            ->minValue(4)
                            ->required(),
                        Toggle::make('require_uppercase')
                            ->label(__('Require Uppercase'))
                            ->default(true),
                        Toggle::make('require_lowercase')
                            ->label(__('Require Lowercase'))
                            ->default(true),
                        Toggle::make('require_numbers')
                            ->label(__('Require Numbers'))
                            ->default(true),
                        Toggle::make('require_special_chars')
                            ->label(__('Require Special Characters'))
                            ->default(true),
                    ])->columns(2),

                Section::make(__('Security Settings'))
                    ->schema([
                        TextInput::make('expires_days')
                            ->label(__('Password Expires (Days)'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('Leave empty for no expiration')),
                        TextInput::make('history_count')
                            ->label(__('Password History Count'))
                            ->numeric()
                            ->default(5)
                            ->minValue(0),
                        TextInput::make('max_login_attempts')
                            ->label(__('Max Login Attempts'))
                            ->numeric()
                            ->default(5)
                            ->minValue(1),
                        TextInput::make('lockout_duration')
                            ->label(__('Lockout Duration'))
                            ->numeric()
                            ->default(30)
                            ->helperText(__('In minutes')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()->title(__('Demo Mode'))->body(DemoModeHelper::getRestrictedMessage())->warning()->send();
            return;
        }

        $this->policy->update($this->form->getState());
        Notification::make()
            ->title(__('Password policy updated successfully!'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Password Policy');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Password Policy Settings');
    }
}
