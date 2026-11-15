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
                    ->rules(['regex:/^[0-9]{10,15}$/'])
                    ->validationMessages([
                        'regex' => 'Nomor telepon wajib berupa angka minimal 10 digit (tanpa huruf/spasi).',
                    ]),

                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
