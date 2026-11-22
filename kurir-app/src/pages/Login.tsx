import { IonContent, IonPage } from '@ionic/react';
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

import {
  getServerUrl,
  type KurirProfile,
  requestApi,
  setServerUrl,
  simpanSesiKurir,
} from '../services/api';

interface ResponseLogin {
  message: string;
  token: string;
  kurir: KurirProfile;
}

const Login: React.FC = () => {
  const navigate = useNavigate();

  // buat ip server
  const [serverUrl, setServerUrlInput] = useState(() => getServerUrl());
  const [tampilEditIp, setTampilEditIp] = useState(false);

  // form login kode kurir dan password
  const [kodeKurir, setKodeKurir] = useState('');
  const [password, setPassword] = useState('');
  const [pesanError, setPesanError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setPesanError('');
    setLoading(true);

    setServerUrl(serverUrl);

    try {
      const hasil = await requestApi<ResponseLogin>('/login', 'POST', {
        kode_kurir: kodeKurir.trim().toUpperCase(),
        password,
      });

      simpanSesiKurir(hasil.token, hasil.kurir);

      if (hasil.kurir.must_change_password) {
        navigate('/reset-password', { replace: true });
      } else {
        navigate('/tugas', { replace: true });
      }
    } catch (err) {
      const pesan = err instanceof Error ? err.message : 'Gagal terhubung ke server.';
      setPesanError(pesan);
    } finally {
      setLoading(false);
    }
  };

  return (
    <IonPage>
      <IonContent fullscreen style={{ '--background': '#ffffff' } as React.CSSProperties}>
        <div
          style={{
            position: 'relative',
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
          {/* tombol ip server */}
          <div
            style={{
              position: 'absolute',
              top: '16px',
              right: '16px',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'flex-end',
              zIndex: 10,
            }}
          >
            <button
              type="button"
              onClick={() => setTampilEditIp(!tampilEditIp)}
              style={{
                backgroundColor: '#F3F4F6',
                color: '#374151',
                border: '1px solid #D1D5DB',
                borderRadius: '8px',
                padding: '6px 10px',
                fontSize: '11px',
                fontWeight: '600',
                cursor: 'pointer',
              }}
            >
              {tampilEditIp ? '✕ Tutup' : '⚙ IP Server'}
            </button>

            {/* inputan ip server */}
            {tampilEditIp && (
              <div
                style={{
                  marginTop: '8px',
                  backgroundColor: '#374151',
                  padding: '10px',
                  borderRadius: '10px',
                  boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                  width: '220px',
                }}
              >
                <label
                  style={{
                    display: 'block',
                    color: '#FBBF24',
                    fontSize: '11px',
                    fontWeight: '700',
                    marginBottom: '4px',
                  }}
                >
                  IP Server Laravel
                </label>
                <input
                  type="text"
                  value={serverUrl}
                  onChange={(e) => setServerUrlInput(e.target.value)}
                  placeholder="http://192.168.137.1:8000"
                  style={{
                    width: '100%',
                    padding: '7px 8px',
                    borderRadius: '6px',
                    border: 'none',
                    backgroundColor: '#F9FAFB',
                    color: '#111827',
                    fontSize: '12px',
                    boxSizing: 'border-box',
                  }}
                />
              </div>
            )}
          </div>

          {/* judul */}
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

          {/* form login */}
          <form
            onSubmit={handleLogin}
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
                marginBottom: '20px',
              }}
            >
              Kurir Login
            </h2>

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
              <label style={{ display: 'block', color: '#E5E7EB', fontSize: '12px', marginBottom: '6px' }}>
                Kode Kurir
              </label>
              <input
                type="text"
                placeholder="Contoh: KUR-01"
                value={kodeKurir}
                onChange={(e) => setKodeKurir(e.target.value)}
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
              <label style={{ display: 'block', color: '#E5E7EB', fontSize: '12px', marginBottom: '6px' }}>
                Password
              </label>
              <input
                type="password"
                placeholder="Masukkan password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
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
                {loading ? 'Memproses...' : 'Login'}
              </button>
            </div>
          </form>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Login;
