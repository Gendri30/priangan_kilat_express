<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
}
