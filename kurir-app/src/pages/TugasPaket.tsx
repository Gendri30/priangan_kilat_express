import { IonContent, IonPage } from '@ionic/react';
import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import HeaderKurir from '../components/HeaderKurir';
import UpdatePaketModal from '../components/UpdatePaketModal';
import {
  formatRupiah,
  getProfilKurir,
  getTokenKurir,
  type PaketTugasItem,
  requestApi,
} from '../services/api';

// Bentuk balasan JSON dari GET /api/paket-dikirim & GET /api/rekap-cod
interface ResponsePaketDikirim {
  total_tugas: number;
  data: PaketTugasItem[];
}

interface ResponseSaldoCod {
  total_belum_disetor: number;
}

const TugasPaket: React.FC = () => {
  const navigate = useNavigate();

  //data dari server
  const [daftarPaket, setDaftarPaket] = useState<PaketTugasItem[]>([]);
  const [saldoCodBelumSetor, setSaldoCodBelumSetor] = useState(0);
  const [loading, setLoading] = useState(true);

  // update status paket
  const [paketTerpilih, setPaketTerpilih] = useState<PaketTugasItem | null>(null);

  // ambil data kurir
  useEffect(() => {
    const token = getTokenKurir();
    const kurir = getProfilKurir();

    // cek sesi login
    if (!token || !kurir) {
      navigate('/login', { replace: true });
      return;
    }

    if (kurir.must_change_password) {
      navigate('/reset-password', { replace: true });
      return;
    }

    // ambil data paket
    let aktif = true;
    Promise.all([
      requestApi<ResponsePaketDikirim>('/paket-dikirim'),
      requestApi<ResponseSaldoCod>('/rekap-cod'),
    ])
      .then(([resTugas, resCod]) => {
        if (!aktif) return;
        setDaftarPaket(resTugas.data || []);
        setSaldoCodBelumSetor(resCod.total_belum_disetor || 0);
      })
      .catch((err) => {
        if (!aktif) return;
        const pesan = err instanceof Error ? err.message : 'Gagal memuat daftar tugas.';
        alert(pesan);
      })
      .finally(() => {
        if (aktif) setLoading(false);
      });

    return () => {
      aktif = false;
    };
  }, [navigate]);

   //refresh setelah update status di simpan
  const muatUlangSetelahUpdate = async () => {
    setLoading(true);
    try {
      const [resTugas, resCod] = await Promise.all([
        requestApi<ResponsePaketDikirim>('/paket-dikirim'),
        requestApi<ResponseSaldoCod>('/rekap-cod'),
      ]);
      setDaftarPaket(resTugas.data || []);
      setSaldoCodBelumSetor(resCod.total_belum_disetor || 0);
    } catch (err) {
      const pesan = err instanceof Error ? err.message : 'Gagal memperbarui daftar tugas.';
      alert(pesan);
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
            fontFamily: 'sans-serif',
            color: '#000000',
            paddingBottom: '32px',
          }}
        >
          {/* header dan navigasi */}
          <HeaderKurir />

          <div style={{ padding: '12px 24px' }}>
            {/* Kartu Ringkasan "Tugas hari ini" */}
            <h3
              style={{
                fontSize: '16px',
                fontWeight: '800',
                marginBottom: '10px',
                marginTop: '4px',
              }}
            >
              Tugas hari ini
            </h3>

            <div
              style={{
                backgroundColor: '#4B5563',
                borderRadius: '20px',
                padding: '18px 20px',
                color: '#E5E7EB',
                fontSize: '13px',
                marginBottom: '24px',
              }}
            >
              <div style={{ display: 'flex', marginBottom: '8px' }}>
                <span style={{ width: '135px' }}>Total paket diantar</span>
                <span style={{ width: '14px' }}>:</span>
                <span>{daftarPaket.length} Paket</span>
              </div>
              <div style={{ display: 'flex', marginBottom: '14px' }}>
                <span style={{ width: '135px' }}>Saldo COD</span>
                <span style={{ width: '14px' }}>:</span>
                <span>{formatRupiah(saldoCodBelumSetor)}</span>
              </div>
              <div
                style={{
                  textAlign: 'center',
                  color: saldoCodBelumSetor > 0 ? '#FF0000' : '#86EFAC',
                  fontWeight: '700',
                  fontSize: '13px',
                }}
              >
                {saldoCodBelumSetor > 0
                  ? 'Belum setor ke kantor'
                  : 'Tidak ada tanggungan setor COD'}
              </div>
            </div>

            {/* Daftar Kartu Paket Berstatus DIKIRIM */}
            <h3
              style={{
                fontSize: '16px',
                fontWeight: '800',
                marginBottom: '10px',
              }}
            >
              Daftar paket
            </h3>

            {loading ? (
              <p style={{ textAlign: 'center', color: '#6B7280', fontSize: '13px' }}>
                Memuat daftar paket dari server...
              </p>
            ) : daftarPaket.length === 0 ? (
              <div
                style={{
                  backgroundColor: '#4B5563',
                  borderRadius: '20px',
                  padding: '20px',
                  color: '#ffffff',
                  textAlign: 'center',
                  fontSize: '13px',
                }}
              >
                Tidak ada paket berstatus DIKIRIM.
              </div>
            ) : (
              daftarPaket.map((paket) => (
                <div
                  key={paket.id}
                  style={{
                    backgroundColor: '#4B5563',
                    borderRadius: '20px',
                    padding: '18px 20px',
                    color: '#E5E7EB',
                    fontSize: '13px',
                    marginBottom: '16px',
                  }}
                >
                  <div
                    style={{
                      textAlign: 'center',
                      fontWeight: '800',
                      fontSize: '14px',
                      color: '#D1D5DB',
                      marginBottom: '14px',
                    }}
                  >
                    {paket.no_resi}
                  </div>

                  <div style={{ display: 'flex', marginBottom: '6px' }}>
                    <span style={{ width: '80px' }}>Penerima</span>
                    <span style={{ width: '14px' }}>:</span>
                    <span style={{ fontWeight: '600', color: '#ffffff' }}>
                      {paket.nama_penerima}
                    </span>
                  </div>
                  <div style={{ display: 'flex', marginBottom: '6px' }}>
                    <span style={{ width: '80px' }}>Alamat</span>
                    <span style={{ width: '14px' }}>:</span>
                    <span>{paket.alamat_penerima || paket.kota_tujuan}</span>
                  </div>
                  <div style={{ display: 'flex', marginBottom: '6px' }}>
                    <span style={{ width: '80px' }}>Tipe</span>
                    <span style={{ width: '14px' }}>:</span>
                    <span>
                      {paket.layanan.toUpperCase()} ({paket.is_cod ? 'COD' : 'NON-COD'})
                    </span>
                  </div>
                  <div style={{ display: 'flex', marginBottom: '16px' }}>
                    <span style={{ width: '80px' }}>Status</span>
                    <span style={{ width: '14px' }}>:</span>
                    <span style={{ fontWeight: '800', color: '#ffffff' }}>
                      {paket.status.toUpperCase()}
                    </span>
                  </div>

                  <div style={{ display: 'flex', justifyContent: 'center' }}>
                    <button
                      onClick={() => setPaketTerpilih(paket)}
                      style={{
                        backgroundColor: '#FF0000',
                        color: '#ffffff',
                        border: 'none',
                        borderRadius: '10px',
                        padding: '8px 24px',
                        fontSize: '13px',
                        fontWeight: '700',
                        cursor: 'pointer',
                      }}
                    >
                      Update Status
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>

          {/* pop up update status */}
          {paketTerpilih && (
            <UpdatePaketModal
              paket={paketTerpilih}
              onClose={() => setPaketTerpilih(null)}
              onSuccess={muatUlangSetelahUpdate}
            />
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default TugasPaket;
