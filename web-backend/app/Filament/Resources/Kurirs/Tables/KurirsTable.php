<?php

namespace App\Filament\Resources\Kurirs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class KurirsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_kurir')
                    ->label('Kode Kurir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama Kurir')
                    ->searchable(),
                TextColumn::make('no_telepon')
                    ->label('No. Telepon')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                ->visible(fn (): bool => Auth::user()?->role === 'admin'),
            ]);
    }
}
