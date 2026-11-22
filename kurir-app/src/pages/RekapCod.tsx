import { IonContent, IonPage } from '@ionic/react';
import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import HeaderKurir from '../components/HeaderKurir';
import {
  formatRupiah,
  getProfilKurir,
  getTokenKurir,
  type RekapCodItem,
  requestApi,
} from '../services/api';

//balesan json dari get api/rekap-cod
interface ResponseRekapCod {
  total_belum_disetor: number;
  total_sudah_disetor: number;
  data: RekapCodItem[];
}

const RekapCod: React.FC = () => {
  const navigate = useNavigate();

  //menyimpan data rekap cod
  const [belumDisetor, setBelumDisetor] = useState(0);
  const [sudahDisetor, setSudahDisetor] = useState(0);
  const [daftarCod, setDaftarCod] = useState<RekapCodItem[]>([]);
  const [loading, setLoading] = useState(true);

  //ambil data rekap cod saat membuka halaman
  useEffect(() => {
    const token = getTokenKurir();
    const kurir = getProfilKurir();

    if (!token || !kurir) {
      navigate('/login', { replace: true });
      return;
    }

    if (kurir.must_change_password) {
      navigate('/reset-password', { replace: true });
      return;
    }

    let aktif = true;
    requestApi<ResponseRekapCod>('/rekap-cod')
      .then((res) => {
        if (!aktif) return;
        setBelumDisetor(res.total_belum_disetor || 0);
        setSudahDisetor(res.total_sudah_disetor || 0);
        setDaftarCod(res.data || []);
      })
      .catch((err) => {
        if (!aktif) return;
        const pesan = err instanceof Error ? err.message : 'Gagal memuat rekap COD.';
        alert(pesan);
      })
      .finally(() => {
        if (aktif) setLoading(false);
      });

    return () => {
      aktif = false;
    };
  }, [navigate]);

 //tampilan nya
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
          {/*header dan naviasi */}
          <HeaderKurir />

          <div style={{ padding: '12px 24px' }}>
            {/*ringkasan cod*/}
            <h3
              style={{
                fontSize: '16px',
                fontWeight: '800',
                marginBottom: '10px',
                marginTop: '4px',
              }}
            >
              Ringkasan COD
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
              <div style={{ display: 'flex', marginBottom: '10px' }}>
                <span style={{ width: '145px' }}>Total Belum Disetor</span>
                <span style={{ width: '14px' }}>:</span>
                <span>{formatRupiah(belumDisetor)}</span>
              </div>
              <div style={{ display: 'flex' }}>
                <span style={{ width: '145px' }}>Total Sudah Disetor</span>
                <span style={{ width: '14px' }}>:</span>
                <span>{formatRupiah(sudahDisetor)}</span>
              </div>
            </div>

            {/*rincian paket cod*/}
            <h3
              style={{
                fontSize: '16px',
                fontWeight: '800',
                marginBottom: '10px',
              }}
            >
              Rincian Paket COD
            </h3>

            {loading ? (
              <p style={{ textAlign: 'center', color: '#6B7280', fontSize: '13px' }}>
                Memuat data COD dari server...
              </p>
            ) : daftarCod.length === 0 ? (
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
                Belum ada paket COD yang terkirim.
              </div>
            ) : (
              daftarCod.map((item) => {
                const sudahSetor = item.status_setoran_cod === 'sudah_disetor';

                return (
                  <div
                    key={item.id}
                    style={{
                      backgroundColor: '#4B5563',
                      borderRadius: '20px',
                      padding: '18px 20px',
                      color: '#E5E7EB',
                      fontSize: '13px',
                      marginBottom: '16px',
                      // bingkai biru otomatis muncul jika status sudah_disetor
                      border: sudahSetor ? '3px solid #0284C7' : '3px solid transparent',
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
                      {item.no_resi}
                    </div>

                    <div style={{ display: 'flex', marginBottom: '6px' }}>
                      <span style={{ width: '80px' }}>Penerima</span>
                      <span style={{ width: '14px' }}>:</span>
                      <span>{item.nama_penerima}</span>
                    </div>
                    <div style={{ display: 'flex', marginBottom: '6px' }}>
                      <span style={{ width: '80px' }}>Waktu</span>
                      <span style={{ width: '14px' }}>:</span>
                      <span>{item.waktu_terkirim || '-'}</span>
                    </div>
                    <div style={{ display: 'flex', marginBottom: '6px' }}>
                      <span style={{ width: '80px' }}>Nilai COD</span>
                      <span style={{ width: '14px' }}>:</span>
                      <span>{formatRupiah(item.nilai_cod)}</span>
                    </div>
                    <div style={{ display: 'flex', marginBottom: '14px' }}>
                      <span style={{ width: '80px' }}>Status</span>
                      <span style={{ width: '14px' }}>:</span>
                      <span style={{ fontWeight: '800', color: '#ffffff' }}>TERKIRIM</span>
                    </div>

                    <div
                      style={{
                        textAlign: 'center',
                        fontWeight: '800',
                        fontSize: '13px',
                        color: sudahSetor ? '#ffffff' : '#FF0000',
                      }}
                    >
                      {sudahSetor ? 'Sudah disetor di loket' : 'Belum disetor di loket'}
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default RekapCod;
