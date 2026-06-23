<?php

namespace App\Filament\Pages;

use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Helpers\DemoModeHelper;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Filament\Concerns\HasPagePermissions;
use Illuminate\Validation\Rules\Password as PasswordRule;
class Profile extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPagePermissions;
    protected static ?string $title = null;
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.profile';

    public ?array $data = [];

    public static function getSlug(?Panel $panel = null): string
    {
        return 'profile';
    }

    public function mount(): void
    {
        $this->form->fill(Auth::user()->only([
            'name', 'email', 'phone_number', 'profile_photo', 'two_factor_enabled'
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('Profile Photo'))
                    ->description(__('Optional. Shown across the app where your profile is displayed.'))
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->label(__('Photo'))
                            ->image()
                            // ->avatar()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
                Section::make(__('User Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
                Section::make(__('Contact Information'))
                    ->schema([
                        TextInput::make('phone_number')
                        ->helperText(__('Your phone number will be used to receive notifications and updates from us.'))
                        ->label(__('Phone Number Ex : +91999999999'))
                            ->required()
                            ->maxLength(25)
                            ->unique(ignoreRecord: true)
                            ->minLength(7)
                            ->rules(['regex:/^\+\d+$/'])
                    ])->columns(1),

                Section::make(__('Two-Factor Authentication'))
                    ->description(__('Enable or disable two-factor authentication for your account.'))
                    ->schema([
                        Toggle::make('two_factor_enabled')
                            ->label(__('Enable Two-Factor Authentication'))
                            ->helperText(__('When enabled, you will be required to enter a 2FA code on login.'))
                            ->reactive(),
                    ])->columns(1),

                Section::make(__('Change Password'))
                    ->description(__('Leave blank to keep your current password.'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('Current password'))
                            ->password()
                            ->revealable()
                            ->requiredWith('new_password'),

                        TextInput::make('new_password')
                            ->label(__('New password'))
                            ->password()
                            ->revealable()
                            ->minLength(8),

                        TextInput::make('new_password_confirmation')
                            ->label(__('Confirm new password'))
                            ->password()
                            ->revealable(),
                    ])->columns(3),

            ])
            ->statePath('data')
            ->model(Auth::user());
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('My Profile');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save changes'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        if (DemoModeHelper::isEnabled()) {
            Notification::make()
                ->title(__('Demo Mode'))
                ->body(DemoModeHelper::getRestrictedMessage())
                ->warning()
                ->send();

            return;
        }

        $user = Auth::user();
        $state = $this->form->getState();

        // Sanitize phone number before validation
        if (isset($state['phone_number'])) {
            $state['phone_number'] = $this->sanitizePhoneNumber($state['phone_number']);
            $this->form->fill($state);
        }

        $this->validate([
            'data.name'  => ['required', 'string', 'max:255'],
            'data.email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'data.phone_number' => [
                'required',
                'regex:/^\+\d+$/',
                'min:7',
                'max:25',
                Rule::unique('users', 'phone_number')->ignore($user->id)
            ],
        ], [
            'data.phone_number.regex' => 'The phone number must be in international format starting with + followed by digits only (e.g., +91999999999).',
            'data.phone_number.min' => 'The phone number must be at least 6 digits after the + sign.',
            'data.phone_number.max' => 'The phone number must not exceed 25 characters including the + sign.',
        ]);

        // Handle password update
        if (! empty($state['new_password'])) {
            $this->validate([
                'data.current_password' => ['required', 'current_password'],
                'data.new_password'     => ['required', 'string', PasswordRule::defaults(), 'same:data.new_password_confirmation'],
            ]);

            $user->password = Hash::make($state['new_password']);
        }

        // Update basic info, profile photo & 2FA toggle
        $user->name = $state['name'];
        $user->email = $state['email'];
        $user->phone_number = $state['phone_number'];
        if (array_key_exists('profile_photo', $state)) {
            $user->profile_photo = $state['profile_photo'];
        }
        $user->two_factor_enabled = $state['two_factor_enabled'] ?? false;

        $user->save();

        Notification::make()
            ->title(__('Profile updated'))
            ->success()
            ->send();
    }

    /**
     * Sanitize phone number by removing all non-numeric characters, spaces, and special characters.
     * Ensures phone number starts with + followed by only numeric digits.
     * 
     * @param string|null $phoneNumber
     * @return string
     */
    protected function sanitizePhoneNumber(?string $phoneNumber): string
    {
        if (empty($phoneNumber)) {
            return '';
        }

        // Remove all characters except digits (we'll add + later)
        $digitsOnly = preg_replace('/[^\d]/', '', trim($phoneNumber));
        
        if (empty($digitsOnly)) {
            return '';
        }
        
        // Remove leading zeros from the digits
        $digitsOnly = ltrim($digitsOnly, '0');
        
        // If all zeros were removed, return empty
        if (empty($digitsOnly)) {
            return '';
        }
        
        // Always add + prefix and return the sanitized number
        return '+' . $digitsOnly;
    }
}
