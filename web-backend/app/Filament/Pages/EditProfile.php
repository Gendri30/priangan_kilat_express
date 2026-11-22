<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('username')
                    ->label('Username')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('current_password')
                    ->label('Password Saat Ini')
                    ->password()
                    ->revealable()
                    ->requiredWith('password')
                    ->currentPassword()
                    ->dehydrated(false),

                TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->revealable()
                    ->rule(Password::min(8))
                    ->different('current_password')
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state)),

                TextInput::make('passwordConfirmation')
                    ->label('Konfirmasi Password Baru')
                    ->password()
                    ->revealable()
                    ->requiredWith('password')
                    ->dehydrated(false),
            ]);
    }
}
