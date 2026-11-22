// 1. IMPORT LIBRARY & FUNGSI SESI
import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

import { getProfilKurir, hapusSesiKurir, requestApi } from '../services/api';

const HeaderKurir: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();

  // Ambil data kurir yang sedang login dari localStorage
  const kurir = getProfilKurir();

  // Penanda tab mana yang sedang dibuka saat ini
  const sedangDiTugas = location.pathname === '/tugas';
  const sedangDiRekap = location.pathname === '/rekap-cod';

  // log out
  const handleLogout = async () => {
    if (!window.confirm('Keluar dari akun kurir?')) return;

    try {
      await requestApi('/logout', 'POST');
    } catch {
      // Tetap lanjutkan keluar meskipun token di server sudah habis
    }

    hapusSesiKurir();
    navigate('/login', { replace: true });
  };

  // header dan navigasi
  return (
    <div style={{ backgroundColor: '#ffffff' }}>
      {/* Baris Atas: Nama Ekspedisi & Tombol Aksi Kurir */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: '16px 20px',
          borderBottom: '1px solid #9CA3AF',
          gap: '8px',
        }}
      >
        <span style={{ fontWeight: '900', fontSize: '15px', color: '#000000' }}>
          Priangan Kilat Express
        </span>

        <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
          {/* Tombol Ganti Password Mandiri */}
          <button
            type="button"
            onClick={() => navigate('/reset-password')}
            title="Ganti password akun"
            style={{
              backgroundColor: '#F3F4F6',
              color: '#374151',
              border: '1px solid #D1D5DB',
              borderRadius: '10px',
              padding: '6px 10px',
              fontSize: '11px',
              fontWeight: '700',
              cursor: 'pointer',
            }}
          >
            Ganti Sandi
          </button>

          {/* Tombol Kode Kurir (Logout) */}
          <button
            type="button"
            onClick={handleLogout}
            title="Klik untuk keluar"
            style={{
              backgroundColor: '#D9D9D9',
              color: '#111827',
              border: 'none',
              borderRadius: '10px',
              padding: '6px 12px',
              fontSize: '11px',
              fontWeight: '700',
              cursor: 'pointer',
            }}
          >
            Kurir {kurir?.kode_kurir || 'KUR-01'}
          </button>
        </div>
      </div>

      {/* Baris Tab Navigasi: Tugas paket & Rekap COD */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-around',
          alignItems: 'center',
          padding: '16px 20px 8px',
        }}
      >
        <button
          type="button"
          onClick={() => navigate('/tugas')}
          style={{
            background: 'none',
            border: 'none',
            borderBottom: sedangDiTugas ? '2px solid #4B5563' : '2px solid transparent',
            paddingBottom: '6px',
            fontSize: '17px',
            fontWeight: '800',
            color: '#000000',
            cursor: 'pointer',
          }}
        >
          Tugas paket
        </button>

        <button
          type="button"
          onClick={() => navigate('/rekap-cod')}
          style={{
            background: 'none',
            border: 'none',
            borderBottom: sedangDiRekap ? '2px solid #4B5563' : '2px solid transparent',
            paddingBottom: '6px',
            fontSize: '17px',
            fontWeight: '800',
            color: '#000000',
            cursor: 'pointer',
          }}
        >
          Rekap COD
        </button>
      </div>
    </div>
  );
};

export default HeaderKurir;
