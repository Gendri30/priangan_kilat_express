<?php

namespace App\Filament\Resources\ZonaTarifs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ZonaTarifsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('zona')
                    ->label('Zona')
                    ->sortable(),
                TextColumn::make('nama_zona')
                    ->label('Nama Wilayah')
                    ->searchable(),
                TextColumn::make('tarif_per_kg')
                    ->label('Tarif per Kg')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => Auth::user()?->role === 'manajer'),
            ]);
    }
}
