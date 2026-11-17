<?php

namespace App\Filament\Resources\Pakets;

use App\Filament\Resources\Pakets\Pages\CreatePaket;
use App\Filament\Resources\Pakets\Pages\EditPaket;
use App\Filament\Resources\Pakets\Pages\ListPakets;
use App\Filament\Resources\Pakets\Schemas\PaketForm;
use App\Filament\Resources\Pakets\Tables\PaketsTable;
use App\Models\Paket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class PaketResource extends Resource
{
    protected static ?string $model = Paket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'no_resi';

    public static function form(Schema $schema): Schema{
        return PaketForm::configure($schema);
    }

    public static function table(Table $table): Table{
        return PaketsTable::configure($table);
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
            'index' => ListPakets::route('/'),
            'create' => CreatePaket::route('/create'),
        ];
    }
    // Hak akses: Hanya Admin yang bisa input paket; edit bebas & hapus dimatikan
    public static function canCreate(): bool{
        return Auth::user()?->role === 'admin';
    }

    // Data paket diupdate lewat tombol aksi status, bukan form edit bebas
    public static function canEdit(Model $record): bool{
        return false;
    }

    public static function canDelete(Model $record): bool{
        return false;
    }

    public static function canDeleteAny(): bool{
        return false;
    }
}
