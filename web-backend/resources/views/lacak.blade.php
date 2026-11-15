<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priangan Kilat Express - Lacak Paket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white min-h-screen flex flex-col items-center pt-12 pb-16 px-4 font-sans text-slate-900">

    <!-- Link Login Petugas (Tipis di pojok kanan atas) -->
    <div class="w-full max-w-2xl flex justify-end mb-4">
        <a href="/admin/login" class="text-xs text-slate-400 hover:text-slate-700 transition">
            Login Petugas &rarr;
        </a>
    </div>

    <!-- Judul Utama -->
    <h1 class="text-3xl sm:text-4xl font-extrabold text-black tracking-tight text-center mb-8">
        Priangan Kilat Express
    </h1>

    <!-- Kotak Pencarian Resi -->
    <form action="{{ route('lacak.resi') }}" method="GET" class="w-full max-w-2xl bg-[#E2E8F0] rounded-lg p-2 flex items-center mb-6">
        <svg class="w-5 h-5 text-slate-500 ml-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
            type="text"
            name="no_resi"
            value="{{ $noResi }}"
            placeholder="PKE-261109-0004"
            required
            class="w-full bg-transparent px-3 py-1.5 text-slate-800 placeholder-slate-400 outline-none text-sm sm:text-base tracking-wide uppercase"
        >
        <button
            type="submit"
            class="bg-[#FF0000] hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-lg text-xs sm:text-sm whitespace-nowrap transition cursor-pointer"
        >
            lacak paket
        </button>
    </form>

    <!-- Kartu Utama Abu-Abu Gelap (#4A5362) -->
    <div class="w-full max-w-2xl bg-[#4A5362] text-white rounded-lg p-6 sm:p-10 min-h-[380px] flex flex-col justify-center shadow-md">

        @if ($tidakDitemukan)
            <!-- Jika Resi Tidak Ditemukan -->
            <div class="text-center py-10">
                <p class="text-lg font-bold tracking-wide text-red-300 mb-2">NOMOR RESI TIDAK DITEMUKAN</p>
                <p class="text-sm text-slate-200">
                    Resi <span class="font-mono font-semibold">{{ strtoupper($noResi) }}</span> belum terdaftar di sistem kami. Periksa kembali kode resi Anda.
                </p>
            </div>

        @elseif ($paket)
            <!-- Jika Resi Ditemukan -->
            <h2 class="text-center text-xl sm:text-2xl font-bold tracking-wider uppercase mb-6">
                INFORMASI PENGIRIMAN
            </h2>

            <!-- Kotak Detail Bergaris Tipis -->
            <div class="border border-slate-300/70 p-5 sm:p-6 mb-8 text-xs sm:text-sm space-y-2.5">
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">Nomor Resi</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-medium">{{ $paket->no_resi }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">Kota Tujuan</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-medium">{{ $paket->kota_tujuan }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">Penerima</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-medium">{{ $paket->nama_penerima }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">No. Telepon</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-medium">{{ $paket->masked_telepon_penerima }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">Layanan</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-medium">{{ $paket->layanan }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-200">Status Terkini</span>
                    <span class="col-span-1 text-slate-200">:</span>
                    <span class="col-span-7 font-bold uppercase">{{ $paket->status }}</span>
                </div>
            </div>

            <!-- Linimasa Perjalanan Paket (Urut dari Awal ke Terbaru) -->
            @php
                $riwayatUrut = $paket->riwayatStatus->sortBy('waktu')->values();
            @endphp

            @if ($riwayatUrut->isNotEmpty())
                <div class="pl-6 sm:pl-10">
                    <div class="relative border-l border-slate-400/70 space-y-5 text-xs">
                        @foreach ($riwayatUrut as $log)
                            <div class="relative pl-6">
                                <!-- Titik Linimasa -->
                                <span class="absolute -left-[4.5px] top-1 w-2 h-2 rounded-full {{ $loop->last ? 'bg-amber-400' : 'bg-slate-400' }}"></span>

                                <div class="grid grid-cols-12 gap-2 items-baseline">
                                    <span class="col-span-4 sm:col-span-4 text-slate-300">
                                        {{ \Illuminate\Support\Carbon::parse($log->waktu)->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="col-span-8 sm:col-span-8 {{ $loop->last ? 'font-bold text-white' : 'text-slate-100' }}">
                                        {{ $log->catatan ?: $log->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <!-- Tampilan Awal Kosong (Persis Frame Kiri Desainmu) -->
            <div class="text-center text-slate-300/60 text-sm">
                Masukkan nomor resi di atas untuk melacak status paket
            </div>
        @endif

    </div>

</body>
</html>
