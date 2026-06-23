<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\HasDemoMode;
use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Helpers\DemoModeHelper;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class UserResource extends Resource
{
    use HasDemoMode;
    use HasResourcePermissions;

    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    public function __construct()
    {
        checkAndAssignUserRole();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('User Information'))
                    ->schema([
                        FileUpload::make('profile_photo')
                            ->label(__('Profile Photo'))
                            ->image()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(2048)
                            ->placeholder(__('Drag & Drop your files or Browse'))
                            ->helperText(__('Optional. JPG, PNG or GIF. Max 2MB.'))
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone_number')
                            ->label(__('Phone Number'))
                            ->tel()
                            ->maxLength(20)
                            ->placeholder(__('+1 555 000 0000')),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText(fn (string $context) => $context === 'edit' ? __('Leave blank to keep current password.') : null),
                        Toggle::make('email_verified_at')
                            ->label(__('Email Verified'))
                            ->helperText(__('Status of email verification'))
                            ->afterStateHydrated(fn ($component, $state) => $component->state($state !== null && $state !== false))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                            ->hiddenOn('create'),
                        Toggle::make('is_active')
                            ->label(__('Account Active'))
                            ->default(true)
                            ->helperText(__('Inactive users cannot log in.')),
                    ])->columns(2),

                Section::make(__('Roles & Access'))
                    ->schema([
                        Select::make('roles')
                            ->label(__('Role'))
                            ->options(
                                Role::where('name', '!=', 'super_admin')
                                    ->get()
                                    ->mapWithKeys(fn ($r) => [
                                        $r->id => match ($r->name) {
                                            'tenant_owner' => 'Business Owner',
                                            'staff'        => 'Staff',
                                            default        => ucwords(str_replace('_', ' ', $r->name)),
                                        },
                                    ])
                                    ->toArray()
                            )
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->multiple(false)
                            ->disabled(fn (?User $record) => $record !== null)
                            ->helperText(fn (?User $record) => $record ? __('Role cannot be changed after user is created.') : null),

                        Select::make('tenant_id')
                            ->label(__('Business'))
                            ->options(Tenant::orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->required(fn ($get) => $get('roles') && Role::find($get('roles'))?->name === 'staff')
                            ->visible(fn ($get) => ! $get('roles') || Role::find($get('roles'))?->name === 'staff')
                            ->helperText(__('The business this staff member belongs to.')),

                        DateTimePicker::make('created_at')
                            ->label(__('Created at'))
                            ->disabled()
                            ->hiddenOn('create'),
                        DateTimePicker::make('updated_at')
                            ->label(__('Updated at'))
                            ->disabled()
                            ->hiddenOn('create'),
                    ]),

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with(['roles', 'ownedTenant', 'tenant', 'provider.tenant']) // Eager load roles + tenant paths
                    ->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'super_admin');
                    });
            })
            ->columns([
                TextColumn::make('row_index')
                    ->label(__('#'))
                    ->rowIndex()
                    ->width('3rem')
                    ->alignStart(),
                ViewColumn::make('name')
                    ->label(__('Name'))
                    ->view('filament.components.table-user-cell')
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label(__('Phone'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('—')),
                TextColumn::make('roles.name')
                    ->label(__('Role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin'  => 'Super Admin',
                        'tenant_owner' => 'Business Owner',
                        'staff'        => 'Staff',
                        'client'       => 'Client',
                        default        => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin'  => 'danger',
                        'tenant_owner' => 'warning',
                        'staff'        => 'info',
                        'client'       => 'success',
                        default        => 'gray',
                    }),
                TextColumn::make('business_name')
                    ->label(__('Business'))
                    ->placeholder(__('—'))
                    ->getStateUsing(fn (User $record) =>
                        $record->ownedTenant?->name       // business owner
                        ?? $record->tenant?->name         // staff with tenant_id
                        ?? $record->provider?->tenant?->name  // staff linked via providers table
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('ownedTenant', fn ($t) => $t->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('provider.tenant', fn ($t) => $t->where('name', 'like', "%{$search}%"));
                        });
                    })
                    ->url(fn (User $record) => ($record->ownedTenant ?? $record->tenant ?? $record->provider?->tenant)
                        ? route('filament.admin.resources.tenants.edit', $record->ownedTenant ?? $record->tenant ?? $record->provider?->tenant)
                        : null)
                    ->color('primary'),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => filled($record->email_verified_at))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label(__('Role'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label(__('Account Status'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive')),

                TernaryFilter::make('email_verified_at')
                    ->label(__('Email Verified'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Verified'))
                    ->falseLabel(__('Not Verified'))
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn () => DemoModeHelper::isEnabled())
                    ->label('')
                    ->tooltip(__('Edit User Details')),
                // ->tooltip(fn () => \App\Helpers\DemoModeHelper::isEnabled() ? \App\Helpers\DemoModeHelper::getRestrictedMessage() : null),
                Action::make('verify')
                    ->label('')
                    ->icon(fn (User $record) => 'heroicon-o-envelope')
                    ->color(fn (User $record) => $record->hasVerifiedEmail() ? 'success' : 'danger')
                    ->tooltip(fn (User $record) => $record->hasVerifiedEmail() ? __('Email Verified') : __('Email Not Verified - Click to Verify'))
                    ->requiresConfirmation(fn (User $record) => ! $record->hasVerifiedEmail())
                    ->action(function (User $record) {
                        if ($record->hasVerifiedEmail()) {
                            // If already verified, show notification
                            Notification::make()
                                ->title(__('Email Already Verified'))
                                ->body(__('This email is already verified.'))
                                ->info()
                                ->send();
                        } else {
                            // Verify the email
                            $record->markEmailAsVerified();
                            Notification::make()
                                ->title(__('Email Verified'))
                                ->body(__('The email has been successfully verified.'))
                                ->success()
                                ->send();
                        }
                    }),
                Action::make('changePassword')
                    // ->label(__('Change Password'))
                    ->tooltip(__('Change Password'))
                    ->label('')
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->required()
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label(__('Confirm Password'))
                            ->password()
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['password'])]);
                        Notification::make()
                            ->title(__('Password updated'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->disabled(fn () => DemoModeHelper::isEnabled())
                    ->tooltip(fn () => DemoModeHelper::isEnabled() ? DemoModeHelper::getRestrictedMessage() : __('Delete User'))
                    ->label('')
                    ->action(function (User $record) {
                        if ($record->hasRole('super_admin')) {
                            Notification::make()
                                ->title(__('Cannot delete super admin'))
                                ->danger()
                                ->send();

                            return; // exit without deleting
                        } else {
                            $record->delete(); // normal delete for others
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->hasRole('super_admin')) {
                                    Notification::make()
                                        ->title(__('Cannot delete super admin users'))
                                        ->danger()
                                        ->send();

                                    return; // stop the bulk delete
                                }
                            }
                            $records->each->delete();
                        }),
                ]),
            ])

            ->headerActions([
                ExportAction::make('export')
                    ->label(__('Export'))
                    ->exports([
                        ExcelExport::make('pdf')
                            ->withWriterType(Excel::DOMPDF)
                            ->fromTable()
                            ->withFilename('export_users'.date('YmdHis').'.pdf'),
                        ExcelExport::make('xlsx')
                            ->withWriterType(Excel::XLSX)
                            ->fromTable(),
                        ExcelExport::make('csv')
                            ->withWriterType(Excel::CSV)
                            ->fromTable(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('User Management');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function getNavigationLabel(): string
    {
        return __('Users');
    }
}
