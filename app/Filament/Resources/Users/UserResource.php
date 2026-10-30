<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'gebruiker';

    protected static ?string $pluralModelLabel = 'Gebruikers';

    protected static string|\UnitEnum|null $navigationGroup = 'Instellingen';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Naam')->required()->maxLength(255),
            TextInput::make('email')->label('E-mail')->email()->required()->maxLength(254)->unique(ignoreRecord: true),
            Select::make('role')->label('Rol')->options(User::ROLES)->placeholder('Geen toegang tot het panel')
                ->helperText('Beheerder: alles. Redacteur: inhoud, documentatie en changelog; geen gebruikers, instellingen, prijzen of functiestatus.')
                ->disabled(fn (?User $record) => $record?->is(auth()->user()) ?? false)
                ->dehydrated(fn (?User $record) => ! ($record?->is(auth()->user()) ?? false)),
            TextInput::make('password')->label('Wachtwoord')->password()->revealable()
                ->rule(Password::min(12)->mixedCase()->numbers())
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText('Minimaal 12 tekens met hoofdletters, kleine letters en cijfers. Leeg laten = niet wijzigen.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naam')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('role')->label('Rol')->badge()->formatStateUsing(fn (?string $state) => User::ROLES[$state] ?? '—')->placeholder('Geen toegang'),
                TextColumn::make('created_at')->label('Aangemaakt')->date('d-m-Y')->sortable(),
            ])
            ->filters([SelectFilter::make('role')->label('Rol')->options(User::ROLES)])
            ->recordActions([
                EditAction::make()->using(fn (User $record, array $data) => self::save($record, $data)),
                DeleteAction::make()->hidden(fn (User $record) => $record->is(auth()->user())),
            ]);
    }

    /**
     * role and is_admin are not mass assignable: set them explicitly.
     */
    public static function save(User $record, array $data): User
    {
        $role = array_key_exists('role', $data) ? $data['role'] : $record->role;
        unset($data['role']);

        $record->fill($data)->save();

        if ($role !== $record->role) {
            $record->assignRole($role);
        }

        return $record;
    }

    public static function createAction(): CreateAction
    {
        return CreateAction::make()->using(fn (array $data) => self::save(new User, $data));
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
