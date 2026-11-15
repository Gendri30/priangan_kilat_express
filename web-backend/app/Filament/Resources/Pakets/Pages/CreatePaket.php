<?php

namespace App\Filament\Resources\Pakets\Pages;

use App\Filament\Resources\Pakets\PaketResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Paket;
use Illuminate\Support\Facades\Auth;


class CreatePaket extends CreateRecord
{
    protected static string $resource = PaketResource::class;
    public function mount(): void{
    abort_unless(Auth::user()?->role === 'admin', 403, 'manajer tidak punya akses');
        parent::mount();
}
    protected function mutateFormDataBeforeCreate(array $data): array{
        $data['no_resi']= Paket::generateNoResi();
        $data['tanggal_masuk']= now();
        $data['status']='Diterima';
        $data['jumlah_gagal']=0;
        $data['perlu_verifikasi']=false;
        $data['status_setoran_cod']= !empty($data['is_cod'])? 'belum_disetor' : 'tidak_cod';

        return $data;
    }
    protected function afterCreate():void{
        $adminNama = Auth::user()?->name ?? 'Admin Loket';

        $this->record->catatRiwayat(status: 'Diterima', pelakuTipe:'Admin', pelakuNama: $adminNama, catatan: 'paket diterima di loket Priangan Kilat Express');
    }
}
