<?php

namespace App\Filament\Resources\Kurirs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class KurirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kurir')
                    ->required(),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('no_telepon')
                    ->tel()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('must_change_password')
                    ->required(),
            ]);
    }
    
}
