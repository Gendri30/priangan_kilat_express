<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kurir;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KurirApiController extends Controller
{
    // 1. POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'kode_kurir' => 'required|string',
            'password' => 'required|string',
        ]);

        $kurir = Kurir::where('kode_kurir', strtoupper(trim($request->kode_kurir)))->first();

        if (! $kurir || ! Hash::check($request->password, $kurir->password)) {
            return response()->json([
                'message' => 'Kode kurir atau password salah.',
            ], 401);
        }

        if (! $kurir->is_active) {
            return response()->json([
                'message' => 'Akun kurir Anda sedang dinonaktifkan. Hubungi Admin loket.',
            ], 403);
        }

        // Hapus token lama agar 1 sesi bersih, lalu buat token baru
        $kurir->tokens()->delete();
        $token = $kurir->createToken('kurir-apk')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'kurir' => [
                'id' => $kurir->id,
                'kode_kurir' => $kurir->kode_kurir,
                'nama' => $kurir->nama,
                'no_telepon' => $kurir->no_telepon,
                'must_change_password' => (bool) $kurir->must_change_password,
            ],
        ]);
    }

    // 2. POST /api/change-password (Wajib saat login pertama / habis reset)
    public function changePassword(Request $request)
    {
        $request->validate([
            'password_baru' => 'required|string|min:8|confirmed',
        ], [
            'password_baru.min' => 'Password baru minimal harus 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        /** @var Kurir $kurir */
        $kurir = $request->user();

        $kurir->update([
            'password' => $request->password_baru,
            'must_change_password' => false,
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
            'kurir' => [
                'id' => $kurir->id,
                'kode_kurir' => $kurir->kode_kurir,
                'nama' => $kurir->nama,
                'must_change_password' => false,
            ],
        ]);
    }

    // 3. GET /api/paket-dikirim (Daftar paket berstatus Dikirim milik kurir ini)
    public function paketDikirim(Request $request)
    {
        /** @var Kurir $kurir */
        $kurir = $request->user();

        if ($kurir->must_change_password) {
            return response()->json([
                'message' => 'Anda wajib mengganti password terlebih dahulu.',
            ], 403);
        }

        $pakets = Paket::where('kurir_id', $kurir->id)
            ->where('status', 'Dikirim')
            ->orderBy('tanggal_masuk', 'asc')
            ->get()
            ->map(function (Paket $p) {
                return [
                    'id' => $p->id,
                    'no_resi' => $p->no_resi,
                    'nama_penerima' => $p->nama_penerima,
                    'no_telepon_penerima' => $p->no_telepon_penerima,
                    'alamat_penerima' => $p->alamat_penerima,
                    'kota_tujuan' => $p->kota_tujuan,
                    'layanan' => $p->layanan,
                    'berat_tagih' => $p->berat_tagih,
                    'is_cod' => (bool) $p->is_cod,
                    'nilai_cod' => (int) $p->nilai_cod,
                    'jumlah_gagal' => (int) $p->jumlah_gagal,
                    'status' => $p->status,
                ];
            });

        return response()->json([
            'total_tugas' => $pakets->count(),
            'data' => $pakets,
        ]);
    }

    // 4. POST /api/paket/{no_resi}/update-status (Terkirim / Gagal Kirim wajib catatan)
    public function updateStatus(Request $request, string $no_resi)
    {
        /** @var Kurir $kurir */
        $kurir = $request->user();

        if ($kurir->must_change_password) {
            return response()->json([
                'message' => 'Anda wajib mengganti password terlebih dahulu.',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:Terkirim,Gagal Kirim',
            'catatan' => 'required_if:status,Gagal Kirim|nullable|string|max:255',
        ], [
            'status.in' => 'Status hanya boleh diisi Terkirim atau Gagal Kirim.',
            'catatan.required_if' => 'Catatan alasan wajib diisi jika status Gagal Kirim.',
        ]);

        $paket = Paket::where('no_resi', strtoupper(trim($no_resi)))
            ->where('kurir_id', $kurir->id)
            ->first();

        if (! $paket) {
            return response()->json([
                'message' => 'Paket tidak ditemukan atau bukan tugas pengantaran Anda.',
            ], 404);
        }

        if ($paket->status !== 'Dikirim') {
            return response()->json([
                'message' => 'Hanya paket berstatus Dikirim yang dapat diperbarui oleh kurir.',
            ], 422);
        }

        if ($request->status === 'Terkirim') {
            $paket->update([
                'status' => 'Terkirim',
            ]);

            $catatan = trim((string) $request->catatan) !== ''
                ? trim((string) $request->catatan)
                : 'Paket telah diterima oleh ' . $paket->nama_penerima;

            $paket->catatRiwayat('Terkirim', 'Kurir', $kurir->nama, $catatan);
        } else {
            $paket->update([
                'status' => 'Gagal Kirim',
                'jumlah_gagal' => $paket->jumlah_gagal + 1,
            ]);

            $paket->catatRiwayat('Gagal Kirim', 'Kurir', $kurir->nama, trim((string) $request->catatan));
        }

        return response()->json([
            'message' => 'Status paket ' . $paket->no_resi . ' berhasil diperbarui menjadi ' . $paket->status . '.',
            'paket' => [
                'no_resi' => $paket->no_resi,
                'status' => $paket->status,
                'jumlah_gagal' => $paket->jumlah_gagal,
            ],
        ]);
    }

    // 5. GET /api/rekap-cod (Daftar paket COD Terkirim & total belum disetor)
    public function rekapCod(Request $request)
    {
        /** @var Kurir $kurir */
        $kurir = $request->user();

        if ($kurir->must_change_password) {
            return response()->json([
                'message' => 'Anda wajib mengganti password terlebih dahulu.',
            ], 403);
        }

        $paketCod = Paket::where('kurir_id', $kurir->id)
            ->where('is_cod', true)
            ->where('status', 'Terkirim')
            ->orderByDesc('updated_at')
            ->get();

        $totalBelumDisetor = (int) $paketCod
            ->where('status_setoran_cod', 'belum_disetor')
            ->sum('nilai_cod');

        $totalSudahDisetor = (int) $paketCod
            ->where('status_setoran_cod', 'sudah_disetor')
            ->sum('nilai_cod');

        $daftar = $paketCod->map(function (Paket $p) {
            return [
                'id' => $p->id,
                'no_resi' => $p->no_resi,
                'nama_penerima' => $p->nama_penerima,
                'nilai_cod' => (int) $p->nilai_cod,
                'status_setoran_cod' => $p->status_setoran_cod,
                'waktu_terkirim' => $p->updated_at?->format('d/m/Y H:i'),
            ];
        })->values();

        return response()->json([
            'total_belum_disetor' => $totalBelumDisetor,
            'total_sudah_disetor' => $totalSudahDisetor,
            'data' => $daftar,
        ]);
    }

    // Tambahan: POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
