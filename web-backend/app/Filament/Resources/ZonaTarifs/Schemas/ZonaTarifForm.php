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
                    ->label('Zona')
                    ->disabled(),

                TextInput::make('nama_zona')
                    ->label('Nama Wilayah Zona')
                    ->disabled(),

                TextInput::make('tarif_per_kg')
                    ->label('Tarif per Kg (Rp)')
                    ->required()
                    ->numeric()
                    ->minValue(1000),
            ]);
    }
}
