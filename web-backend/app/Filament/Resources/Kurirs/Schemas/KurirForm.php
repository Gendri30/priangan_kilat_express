<?php

namespace App\Filament\Resources\Kurirs\Schemas;

use App\Models\Kurir;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KurirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kurir')
                    ->label('Kode kurir')
                    ->placeholder('KUR-01')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('nama')
                    ->label('Nama')
                    ->required(),

                TextInput::make('no_telepon')
                    ->label('No telepon')
                    ->tel()
                    ->required()
                    ->rules(['regex:/^[0-9]{10,15}$/'])
                    ->validationMessages([
                        'regex' => 'Nomor telepon wajib berisi angka saja (minimal 10 digit).',
                    ]),

                TextInput::make('password')
                    ->label('Password')
                    ->helperText('Minimal 8 karakter. Kosongkan saat edit jika tidak ingin mereset password.')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),

                Toggle::make('is_active')
                    ->label('Is active')
                    ->default(true)
                    ->disabled(fn (?Kurir $record): bool => $record !== null && $record->countPaketDikirim() > 0)
                    ->helperText(fn (?Kurir $record): ?string => ($record !== null && $record->countPaketDikirim() > 0)
                        ? 'Tidak bisa dinonaktifkan karena masih membawa paket berstatus Dikirim.'
                        : null),

                Toggle::make('must_change_password')
                    ->label('Must change password')
                    ->default(true),
            ]);
    }
}
