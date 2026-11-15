<?php

namespace App\Filament\Resources\Kurirs;

use App\Filament\Resources\Kurirs\Pages\CreateKurir;
use App\Filament\Resources\Kurirs\Pages\EditKurir;
use App\Filament\Resources\Kurirs\Pages\ListKurirs;
use App\Filament\Resources\Kurirs\Schemas\KurirForm;
use App\Filament\Resources\Kurirs\Tables\KurirsTable;
use App\Models\Kurir;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class KurirResource extends Resource
{
    protected static ?string $model = Kurir::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return KurirForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KurirsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKurirs::route('/'),
            'create' => CreateKurir::route('/create'),
            'edit' => EditKurir::route('/{record}/edit'),
        ];
    }
    //hak akses admin
    public static function canCreate(): bool{
        return Auth::user()?->role === 'admin';
    }
    public static function canEdit(Model $record): bool{
        return Auth::user()?->role ==='admin';
    }
    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
