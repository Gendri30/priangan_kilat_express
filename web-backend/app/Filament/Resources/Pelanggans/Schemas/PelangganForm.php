<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('no_telepon')
                    ->tel()
                    ->required(),
                Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    //hak akses admin
    public static function canCreate(): bool{
        return Auth::user()?->role === 'admin';
    }
    public static function canEdit(Model $record): bool{
        return Auth::user()?->role ==='admin';
    }
    public static function canDelete(Model $record): bool{
        return false;
    }
}
