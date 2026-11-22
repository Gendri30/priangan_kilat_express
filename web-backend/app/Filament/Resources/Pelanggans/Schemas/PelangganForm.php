<?php

namespace App\Filament\Resources\Pelanggans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Lengkap Pengirim')
                    ->required()
                    ->maxLength(255),

                TextInput::make('no_telepon')
                    ->label('No. Telepon')
                    ->tel()
                    ->required()
                    ->minLength(10)
                    ->maxLength(14)
                    ->rules(['regex:/^08[0-9]+$/'])
                    ->validationMessages([
                        'regex' => 'Nomor telepon wajib berisi angka saja dan diawali 08.',
                    ]),

                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
