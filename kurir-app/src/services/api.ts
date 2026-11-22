const DEFAULT_SERVER_URL = 'http://192.168.137.1:8000';

// Mengambil alamat server dari memori HP
export function getServerUrl(): string {
  return (localStorage.getItem('server_url') || DEFAULT_SERVER_URL).replace(/\/+$/, '');
}

// Menyimpan alamat IP server baru ke memori HP
export function setServerUrl(url: string): void {
  const bersih = url.trim().replace(/\/+$/, '');
  localStorage.setItem('server_url', bersih || DEFAULT_SERVER_URL);
}

// CETAKAN TIPE DATA SESUAI BALASAN JSON DARI LARAVEL
export interface KurirProfile {
  id: number;
  kode_kurir: string;
  nama: string;
  no_telepon?: string;
  must_change_password: boolean;
}

export interface PaketTugasItem {
  id: number;
  no_resi: string;
  nama_penerima: string;
  no_telepon_penerima?: string;
  alamat_penerima: string;
  kota_tujuan?: string;
  layanan: string;
  berat_tagih?: number;
  is_cod: boolean;
  nilai_cod: number;
  jumlah_gagal?: number;
  status: 'Dikirim' | 'Terkirim' | 'Gagal Kirim';
}

export interface RekapCodItem {
  id: number;
  no_resi: string;
  nama_penerima: string;
  nilai_cod: number;
  status_setoran_cod: 'belum_disetor' | 'sudah_disetor';
  waktu_terkirim: string;
}

// PENYIMPAN TOKEN SANCTUM & PROFIL KURIR DI MEMORI LOKAL
export function simpanSesiKurir(token: string, kurir: KurirProfile): void {
  localStorage.setItem('kurir_token', token);
  localStorage.setItem('kurir_data', JSON.stringify(kurir));
}

export function getTokenKurir(): string | null {
  return localStorage.getItem('kurir_token');
}

export function getProfilKurir(): KurirProfile | null {
  const raw = localStorage.getItem('kurir_data');
  return raw ? (JSON.parse(raw) as KurirProfile) : null;
}

export function hapusSesiKurir(): void {
  localStorage.removeItem('kurir_token');
  localStorage.removeItem('kurir_data');
}

// PENGUBAH ANGKA KE FORMAT RUPIAH ("RP 150.000")
export function formatRupiah(nominal: number): string {
  return 'RP ' + Number(nominal || 0).toLocaleString('id-ID');
}

// FUNGSI PUSAT UNTUK MENGHUBUNGI API LARAVEL
export async function requestApi<T = Record<string, unknown>>(
  endpoint: string,
  method: 'GET' | 'POST' = 'GET',
  body?: Record<string, unknown>
): Promise<T> {
  const token = getTokenKurir();
  const baseUrl = getServerUrl();

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };

  // Jika sudah login, tempelkan token Sanctum
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${baseUrl}/api${endpoint}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });

  const data = (await response.json().catch(() => ({}))) as { message?: string } & T;

  if (!response.ok) {
    throw new Error(data.message || 'Gagal memproses permintaan ke server.');
  }

  return data;
}
