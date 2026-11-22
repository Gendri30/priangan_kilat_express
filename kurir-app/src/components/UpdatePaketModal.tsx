import React, { useState } from 'react';

import { formatRupiah, type PaketTugasItem, requestApi } from '../services/api';

// Data (Props) yang diterima dari halaman TugasPaket
interface UpdatePaketModalProps {
  paket: PaketTugasItem;
  onClose: () => void;
  onSuccess: () => void;
}

const UpdatePaketModal: React.FC<UpdatePaketModalProps> = ({
  paket,
  onClose,
  onSuccess,
}) => {
  // STATE PILIHAN RADIO BUTTON, CATATAN KENDALA, & LOADING
  const [statusPilihan, setStatusPilihan] = useState<'Terkirim' | 'Gagal Kirim'>('Terkirim');
  const [catatan, setCatatan] = useState('');
  const [saving, setSaving] = useState(false);

  // FUNGSI SAAT TOMBOL HIJAU "Simpan" DIKLIK
  const handleSimpan = async (e: React.FormEvent) => {
    e.preventDefault();

    // Validasi wajib: Gagal Kirim harus menyertakan alasan/kendala
    if (statusPilihan === 'Gagal Kirim' && !catatan.trim()) {
      alert('Alasan/Kendala wajib diisi jika memilih GAGAL KIRIM!');
      return;
    }

    setSaving(true);
    try {
      // kirim perubahan status ke update-status
      const hasil = await requestApi<{ message: string }>(
        `/paket/${paket.no_resi}/update-status`,
        'POST',
        {
          status: statusPilihan,
          catatan: catatan.trim(),
        }
      );

      alert(hasil.message || 'Status paket berhasil diperbarui!');
      onSuccess(); // Muat ulang daftar paket terbaru dari server
      onClose(); // Tutup kotak pop-up
    } catch (err) {
      const pesan = err instanceof Error ? err.message : 'Gagal memperbarui status paket.';
      alert(pesan);
    } finally {
      setSaving(false);
    }
  };

  // TAMPILAN KOTAK POP-UP
  return (
    <div
      style={{
        position: 'fixed',
        inset: 0,
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '20px',
        zIndex: 1000,
        fontFamily: 'sans-serif',
      }}
    >
      <div
        style={{
          width: '100%',
          maxWidth: '300px',
          backgroundColor: '#374151',
          borderRadius: '14px',
          overflow: 'hidden',
          color: '#E5E7EB',
          fontSize: '12px',
          boxShadow: '0 10px 25px rgba(0, 0, 0, 0.4)',
        }}
      >
        {/* Judul Pop-up */}
        <div
          style={{
            backgroundColor: '#4B5563',
            padding: '12px 16px',
            textAlign: 'center',
            color: '#ffffff',
            fontWeight: '700',
            fontSize: '15px',
            borderBottom: '1px solid #6B7280',
          }}
        >
          Update Status Paket
        </div>

        {/* Form Pilihan Status */}
        <form onSubmit={handleSimpan} style={{ padding: '16px 18px' }}>
          <div style={{ display: 'flex', marginBottom: '4px' }}>
            <span style={{ width: '70px', color: '#D1D5DB' }}>Resi</span>
            <span style={{ width: '10px' }}>:</span>
            <span>{paket.no_resi}</span>
          </div>
          <div style={{ display: 'flex', marginBottom: '4px' }}>
            <span style={{ width: '70px', color: '#D1D5DB' }}>Penerima</span>
            <span style={{ width: '10px' }}>:</span>
            <span>{paket.nama_penerima}</span>
          </div>
          <div style={{ display: 'flex', marginBottom: '16px' }}>
            <span style={{ width: '70px', color: '#D1D5DB' }}>Tagihan</span>
            <span style={{ width: '10px' }}>:</span>
            <span>
              {paket.is_cod ? `${formatRupiah(paket.nilai_cod)} (COD)` : 'RP 0 (NON-COD)'}
            </span>
          </div>

          {/* Pilihan 1: TERKIRIM */}
          <label
            style={{
              display: 'flex',
              alignItems: 'flex-start',
              gap: '8px',
              marginBottom: '12px',
              cursor: 'pointer',
            }}
          >
            <input
              type="radio"
              name="statusPaket"
              checked={statusPilihan === 'Terkirim'}
              onChange={() => setStatusPilihan('Terkirim')}
              style={{ marginTop: '2px' }}
            />
            <div>
              <div style={{ color: '#ffffff', fontWeight: '700', fontSize: '12px' }}>
                TERKIRIM
              </div>
              {paket.is_cod && (
                <div style={{ color: '#D1D5DB', fontSize: '11px', marginTop: '2px' }}>
                  (Uang COD {formatRupiah(paket.nilai_cod)} diterima)
                </div>
              )}
            </div>
          </label>

          {/* Pilihan 2: GAGAL KIRIM */}
          <label
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              marginBottom: '8px',
              cursor: 'pointer',
            }}
          >
            <input
              type="radio"
              name="statusPaket"
              checked={statusPilihan === 'Gagal Kirim'}
              onChange={() => setStatusPilihan('Gagal Kirim')}
            />
            <span style={{ color: '#ffffff', fontWeight: '700', fontSize: '12px' }}>
              GAGAL KIRIM
            </span>
          </label>

          {/* Kotak Input Alasan/Kendala */}
          <div style={{ marginLeft: '22px', marginBottom: '18px' }}>
            <input
              type="text"
              value={catatan}
              onChange={(e) => setCatatan(e.target.value)}
              placeholder="Alasan/Kendala"
              style={{
                width: '100%',
                backgroundColor: '#1F2937',
                color: '#ffffff',
                border: 'none',
                borderRadius: '6px',
                padding: '8px 10px',
                fontSize: '12px',
                boxSizing: 'border-box',
                outline: 'none',
              }}
            />
          </div>

          {/* Tombol Batal & Simpan */}
          <div style={{ display: 'flex', justifyContent: 'center', gap: '20px' }}>
            <button
              type="button"
              onClick={onClose}
              disabled={saving}
              style={{
                backgroundColor: '#FF0000',
                color: '#ffffff',
                border: 'none',
                borderRadius: '10px',
                padding: '6px 22px',
                fontSize: '12px',
                fontWeight: '700',
                cursor: 'pointer',
              }}
            >
              Batal
            </button>

            <button
              type="submit"
              disabled={saving}
              style={{
                backgroundColor: '#16A34A',
                color: '#ffffff',
                border: 'none',
                borderRadius: '10px',
                padding: '6px 22px',
                fontSize: '12px',
                fontWeight: '700',
                cursor: saving ? 'not-allowed' : 'pointer',
                opacity: saving ? 0.7 : 1,
              }}
            >
              {saving ? '...' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default UpdatePaketModal;
