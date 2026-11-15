<?php

namespace App\Filament\Resources\ZonaTarifs;

use App\Filament\Resources\ZonaTarifs\Pages\CreateZonaTarif;
use App\Filament\Resources\ZonaTarifs\Pages\EditZonaTarif;
use App\Filament\Resources\ZonaTarifs\Pages\ListZonaTarifs;
use App\Filament\Resources\ZonaTarifs\Schemas\ZonaTarifForm;
use App\Filament\Resources\ZonaTarifs\Tables\ZonaTarifsTable;
use App\Models\ZonaTarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ZonaTarifResource extends Resource
{
    protected static ?string $model = ZonaTarif::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_zona';

    public static function form(Schema $schema): Schema
    {
        return ZonaTarifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ZonaTarifsTable::configure($table);
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
            'index' => ListZonaTarifs::route('/'),
            'create' => CreateZonaTarif::route('/create'),
            'edit' => EditZonaTarif::route('/{record}/edit'),
        ];
    }

    //hanya manajer yang bisa liat
    public static function canViewAny(): bool
{
    return Auth::user()?->role === 'manajer';
}
}
