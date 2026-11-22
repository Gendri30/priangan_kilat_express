import { IonContent, IonPage } from '@ionic/react';
import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import {
  getProfilKurir,
  getTokenKurir,
  type KurirProfile,
  requestApi,
  simpanSesiKurir,
} from '../services/api';

interface ResponseChangePassword {
  message: string;
  kurir: KurirProfile;
}

const ResetPassword: React.FC = () => {
  const navigate = useNavigate();

  //input form password baru
  const [kurir] = useState<KurirProfile | null>(() => getProfilKurir());
  const [passwordBaru, setPasswordBaru] = useState('');
  const [konfirmasiPassword, setKonfirmasiPassword] = useState('');
  const [pesanError, setPesanError] = useState('');
  const [loading, setLoading] = useState(false);
  //cek sesi login
  useEffect(() => {
    const token = getTokenKurir();
    if (!token || !kurir) {
      navigate('/login', { replace: true });
    }
  }, [kurir, navigate]);

  //validasi dan kirim pass baru ke api
  const handleSimpan = async (e: React.FormEvent) => {
    e.preventDefault();
    setPesanError('');

    if (passwordBaru.length < 8) {
      setPesanError('Password baru minimal harus 8 karakter.');
      return;
    }

    if (passwordBaru !== konfirmasiPassword) {
      setPesanError('Konfirmasi password baru tidak cocok.');
      return;
    }

    setLoading(true);

    try {
      //kirim ke POST /api/change-password
      const hasil = await requestApi<ResponseChangePassword>('/change-password', 'POST', {
        password_baru: passwordBaru,
        password_baru_confirmation: konfirmasiPassword,
      });

      //mengubah status must_change_passowrd jadi false
      const tokenAktif = getTokenKurir();
      if (tokenAktif && hasil.kurir) {
        simpanSesiKurir(tokenAktif, hasil.kurir);
      }

      alert(hasil.message || 'Password berhasil diperbarui!');
      navigate('/tugas', { replace: true });
    } catch (err) {
      const pesan = err instanceof Error ? err.message : 'Gagal memperbarui password.';
      setPesanError(pesan);
    } finally {
      setLoading(false);
    }
  };

  //ui
  return (
    <IonPage>
      <IonContent fullscreen style={{ '--background': '#ffffff' } as React.CSSProperties}>
        <div
          style={{
            minHeight: '100vh',
            backgroundColor: '#ffffff',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            padding: '24px',
            fontFamily: 'sans-serif',
          }}
        >
          <h1
            style={{
              fontSize: '22px',
              fontWeight: '900',
              color: '#000000',
              marginBottom: '24px',
              textAlign: 'center',
            }}
          >
            Priangan Kilat Express
          </h1>

          <form
            onSubmit={handleSimpan}
            style={{
              width: '100%',
              maxWidth: '320px',
              backgroundColor: '#4B5563',
              borderRadius: '20px',
              padding: '28px 22px',
              boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
              boxSizing: 'border-box',
            }}
          >
            <h2
              style={{
                color: '#ffffff',
                fontSize: '18px',
                fontWeight: '700',
                textAlign: 'center',
                marginTop: 0,
                marginBottom: '6px',
              }}
            >
              Ganti Password
            </h2>

            <p
              style={{
                color: '#D1D5DB',
                fontSize: '12px',
                textAlign: 'center',
                marginTop: 0,
                marginBottom: '18px',
              }}
            >
              {kurir?.must_change_password
                ? `Akun ${kurir.kode_kurir} (${kurir.nama}) wajib ganti sandi awal`
                : `Ubah kata sandi akun ${kurir?.kode_kurir || 'Kurir'} (min. 8 karakter)`}
            </p>

            {pesanError && (
              <div
                style={{
                  backgroundColor: '#FEE2E2',
                  color: '#B91C1C',
                  padding: '8px 12px',
                  borderRadius: '8px',
                  fontSize: '12px',
                  marginBottom: '14px',
                  textAlign: 'center',
                  fontWeight: '600',
                }}
              >
                {pesanError}
              </div>
            )}

            <div style={{ marginBottom: '16px' }}>
              <label
                style={{
                  display: 'block',
                  color: '#E5E7EB',
                  fontSize: '12px',
                  marginBottom: '6px',
                }}
              >
                Masukkan Password Baru
              </label>
              <input
                type="password"
                placeholder="Minimal 8 karakter"
                value={passwordBaru}
                onChange={(e) => setPasswordBaru(e.target.value)}
                required
                style={{
                  width: '100%',
                  padding: '10px 12px',
                  borderRadius: '8px',
                  border: 'none',
                  backgroundColor: '#D9D9D9',
                  color: '#111827',
                  fontSize: '14px',
                  boxSizing: 'border-box',
                }}
              />
            </div>

            <div style={{ marginBottom: '24px' }}>
              <label
                style={{
                  display: 'block',
                  color: '#E5E7EB',
                  fontSize: '12px',
                  marginBottom: '6px',
                }}
              >
                Konfirmasi Password Baru
              </label>
              <input
                type="password"
                placeholder="Ulangi password baru"
                value={konfirmasiPassword}
                onChange={(e) => setKonfirmasiPassword(e.target.value)}
                required
                style={{
                  width: '100%',
                  padding: '10px 12px',
                  borderRadius: '8px',
                  border: 'none',
                  backgroundColor: '#D9D9D9',
                  color: '#111827',
                  fontSize: '14px',
                  boxSizing: 'border-box',
                }}
              />
            </div>

            <div style={{ display: 'flex', justifyContent: 'center' }}>
              <button
                type="submit"
                disabled={loading}
                style={{
                  backgroundColor: '#FF0000',
                  color: '#ffffff',
                  border: 'none',
                  borderRadius: '10px',
                  padding: '10px 36px',
                  fontSize: '14px',
                  fontWeight: '700',
                  cursor: loading ? 'not-allowed' : 'pointer',
                  opacity: loading ? 0.7 : 1,
                }}
              >
                {loading ? 'Menyimpan...' : 'Simpan'}
              </button>
            </div>

            {/* buat ganti password di halaman utama*/}
            {kurir && !kurir.must_change_password && (
              <div style={{ textAlign: 'center', marginTop: '14px' }}>
                <button
                  type="button"
                  onClick={() => navigate('/tugas', { replace: true })}
                  style={{
                    background: 'none',
                    border: 'none',
                    color: '#D1D5DB',
                    fontSize: '12px',
                    textDecoration: 'underline',
                    cursor: 'pointer',
                  }}
                >
                  Batal
                </button>
              </div>
            )}
          </form>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default ResetPassword;
