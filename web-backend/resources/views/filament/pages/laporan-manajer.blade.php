<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 24px; font-family: sans-serif;">

        {{-- KARTU FILTER TANGGAL --}}
        <div style="background-color: #4A5362; color: #FFFFFF; border-radius: 10px; padding: 24px 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h2 style="text-align: center; font-size: 16px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 18px 0;">
                FILTER PERIODE LAPORAN
            </h2>

            <div style="border: 1px solid rgba(203, 213, 225, 0.55); padding: 18px; display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-size: 12px; color: #E2E8F0; margin-bottom: 6px; font-weight: 600;">
                        Dari Tanggal
                    </label>
                    <input
                        type="date"
                        wire:model.live="tanggalMulai"
                        style="width: 100%; background-color: #E2E8F0; color: #0F172A; border: none; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; outline: none;"
                    >
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-size: 12px; color: #E2E8F0; margin-bottom: 6px; font-weight: 600;">
                        Sampai Tanggal
                    </label>
                    <input
                        type="date"
                        wire:model.live="tanggalSelesai"
                        style="width: 100%; background-color: #E2E8F0; color: #0F172A; border: none; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-weight: 600; outline: none;"
                    >
                </div>

                <div>
                    <button
                        type="button"
                        wire:click="resetFilter"
                        style="background-color: #FF0000; color: #FFFFFF; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer;"
                    >
                        Reset Bulan Ini
                    </button>
                </div>
            </div>
        </div>

        {{-- 1. LAPORAN PENGIRIMAN PER STATUS --}}
        <div style="background-color: #4A5362; color: #FFFFFF; border-radius: 10px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h2 style="text-align: center; font-size: 18px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 6px 0;">
                1. LAPORAN PENGIRIMAN (PER STATUS)
            </h2>
            <p style="text-align: center; font-size: 12px; color: #CBD5E1; margin: 0 0 20px 0;">
                Rekap jumlah paket masuk pada periode terpilih berdasarkan status saat ini
            </p>

            <div style="border: 1px solid rgba(203, 213, 225, 0.55); padding: 16px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.55); color: #CBD5E1; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 10px 12px; text-align: left;">Status Paket</th>
                            <th style="padding: 10px 12px; text-align: right;">Jumlah Paket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->laporanPengiriman['rows'] as $row)
                            <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.25);">
                                <td style="padding: 11px 12px; color: #F8FAFC; font-weight: 500;">{{ $row['status'] }}</td>
                                <td style="padding: 11px 12px; text-align: right; color: #FFFFFF; font-weight: 700;">
                                    {{ number_format($row['jumlah'], 0, ',', '.') }} Paket
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1px solid rgba(203, 213, 225, 0.7);">
                            <td style="padding: 12px; font-weight: 800; text-transform: uppercase; color: #FFFFFF;">
                                Total Keseluruhan Paket
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; font-size: 15px; color: #FBBF24;">
                                {{ number_format($this->laporanPengiriman['total'], 0, ',', '.') }} Paket
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 2. LAPORAN PENDAPATAN PER ZONA --}}
        <div style="background-color: #4A5362; color: #FFFFFF; border-radius: 10px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h2 style="text-align: center; font-size: 18px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 6px 0;">
                2. LAPORAN PENDAPATAN PER ZONA
            </h2>
            <p style="text-align: center; font-size: 12px; color: #CBD5E1; margin: 0 0 20px 0;">
                Total ongkos kirim dan biaya layanan COD berdasarkan tanggal masuk paket
            </p>

            <div style="border: 1px solid rgba(203, 213, 225, 0.55); padding: 16px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.55); color: #CBD5E1; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 10px 12px; text-align: left;">Zona Wilayah</th>
                            <th style="padding: 10px 12px; text-align: right;">Jumlah Paket</th>
                            <th style="padding: 10px 12px; text-align: right;">Total Ongkir</th>
                            <th style="padding: 10px 12px; text-align: right;">Total Biaya COD</th>
                            <th style="padding: 10px 12px; text-align: right;">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->laporanPendapatanZona['rows'] as $row)
                            <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.25);">
                                <td style="padding: 11px 12px; color: #F8FAFC; font-weight: 500;">{{ $row['zona'] }}</td>
                                <td style="padding: 11px 12px; text-align: right; color: #E2E8F0;">
                                    {{ number_format($row['jumlah_paket'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; color: #E2E8F0;">
                                    Rp {{ number_format($row['ongkos_kirim'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; color: #E2E8F0;">
                                    Rp {{ number_format($row['biaya_cod'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; color: #FFFFFF; font-weight: 700;">
                                    Rp {{ number_format($row['total_pendapatan'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1px solid rgba(203, 213, 225, 0.7);">
                            <td style="padding: 12px; font-weight: 800; text-transform: uppercase; color: #FFFFFF;">Total Keseluruhan</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #FFFFFF;">
                                {{ number_format($this->laporanPendapatanZona['grand_paket'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #FFFFFF;">
                                Rp {{ number_format($this->laporanPendapatanZona['grand_ongkir'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #FFFFFF;">
                                Rp {{ number_format($this->laporanPendapatanZona['grand_biaya_cod'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; font-size: 15px; color: #FBBF24;">
                                Rp {{ number_format($this->laporanPendapatanZona['grand_total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 3. LAPORAN REKAP COD PER KURIR --}}
        <div style="background-color: #4A5362; color: #FFFFFF; border-radius: 10px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <h2 style="text-align: center; font-size: 18px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 6px 0;">
                3. REKAPITULASI COD PER KURIR
            </h2>
            <p style="text-align: center; font-size: 12px; color: #CBD5E1; margin: 0 0 20px 0;">
                Rincian nilai COD dari paket COD berstatus Terkirim (Belum Disetor vs Sudah Disetor)
            </p>

            <div style="border: 1px solid rgba(203, 213, 225, 0.55); padding: 16px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.55); color: #CBD5E1; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 10px 12px; text-align: left;">Kode Kurir</th>
                            <th style="padding: 10px 12px; text-align: left;">Nama Kurir</th>
                            <th style="padding: 10px 12px; text-align: right;">Paket COD Terkirim</th>
                            <th style="padding: 10px 12px; text-align: right;">Belum Disetor</th>
                            <th style="padding: 10px 12px; text-align: right;">Sudah Disetor</th>
                            <th style="padding: 10px 12px; text-align: right;">Total Nilai COD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->laporanCodKurir['rows'] as $row)
                            <tr style="border-bottom: 1px solid rgba(203, 213, 225, 0.25);">
                                <td style="padding: 11px 12px; font-family: monospace; font-weight: 700; color: #FFFFFF;">
                                    {{ $row['kode_kurir'] }}
                                </td>
                                <td style="padding: 11px 12px; color: #F8FAFC;">{{ $row['nama_kurir'] }}</td>
                                <td style="padding: 11px 12px; text-align: right; color: #E2E8F0;">
                                    {{ number_format($row['jumlah_paket'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; font-weight: 700; color: #FCA5A5;">
                                    Rp {{ number_format($row['belum_disetor'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; font-weight: 700; color: #86EFAC;">
                                    Rp {{ number_format($row['sudah_disetor'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 11px 12px; text-align: right; font-weight: 700; color: #FFFFFF;">
                                    Rp {{ number_format($row['total_cod'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1px solid rgba(203, 213, 225, 0.7);">
                            <td colspan="3" style="padding: 12px; font-weight: 800; text-transform: uppercase; color: #FFFFFF;">
                                Total Seluruh Kurir
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; color: #FCA5A5;">
                                Rp {{ number_format($this->laporanCodKurir['total_belum_disetor'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; color: #86EFAC;">
                                Rp {{ number_format($this->laporanCodKurir['total_sudah_disetor'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; font-size: 15px; color: #FBBF24;">
                                Rp {{ number_format($this->laporanCodKurir['grand_total_cod'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
