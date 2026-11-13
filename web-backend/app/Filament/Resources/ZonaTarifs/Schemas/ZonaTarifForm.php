<?php

namespace App\Filament\Resources\ZonaTarifs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ZonaTarifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('zona')
                    ->required()
                    ->numeric(),
                TextInput::make('nama_zona')
                    ->required(),
                TextInput::make('tarif_per_kg')
                    ->required()
                    ->numeric(),
            ]);
    }
}
