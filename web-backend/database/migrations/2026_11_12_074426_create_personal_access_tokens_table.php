<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password');
            $table->enum('role',['admin', 'manajer'])->default('admin');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('kurirs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kurir');
            $table->string('nama');
            $table->string('no_telepon');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);
            $table->timestamps();
        });
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_telepon');
            $table->text('alamat');
            $table->timestamps();
        });
        Schema::create('zona_tarifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('zona')->unique();
            $table->string('nama_zona');
            $table->unsignedInteger('tarif_per_kg');
            $table->timestamps();
        });
        Schema::create('pakets', function (Blueprint $table) {
            $table->id();
            $table->string('no_resi', 30)->unique();
            $table->dateTime('tanggal_masuk');
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->onDelete('cascade');
            $table->string('nama_penerima');
            $table->string('no_telepon_penerima',20);
            $table->text('alamat_penerima');
            $table->string('kota_tujuan');
            $table->unsignedBigInteger('zona');
            $table->boolean('is_zona_manual')->default(false);
            $table->decimal('berat_aktual', 8,2)->nullable();
            $table->unsignedInteger('berat_tagih')->default(1);
            $table->boolean('perlu_verifikasi')->default(false);
            $table->enum('layanan',['Reguler','Express'])->default('Reguler');
            $table->unsignedInteger('tarif_dasar_snapshot');
            $table->unsignedInteger('ongkos_kirim');
            $table->boolean('is_cod')->default(false);
            $table->unsignedInteger('nilai_cod')->default(0);
            $table->unsignedInteger('biaya_cod')->default(0);
            $table->enum('status_setoran_cod',['belum_disetor','sudah_disetor','tidak_cod'])->default('tidak_cod');
            $table->foreignId('kurir_id')->nullable()->constrained('kurirs')->onDelete('set null');
            $table->enum('status',['Diterima','Transit','Dikirim','Gagal Kirim','Terkirim','Retur'])->default('Diterima');
            $table->unsignedTinyInteger('jumlah_gagal')->default(0);
            $table->timestamps();
        });
        Schema::create('riwayat_status_pakets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('pakets')->onDelete('cascade');
            $table->string('status');
            $table->enum('pelaku_tipe',['Admin','Kurir','Sistem']);
            $table->string('pelaku_nama');
            $table->text('catatan')->nullable();
            $table->dateTime('waktu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('kurirs');
        Schema::dropIfExists('pelanggans');
        Schema::dropIfExists('zona_tarifs');
        Schema::dropIfExists('pakets');
        Schema::dropIfExists('riwayat_status_pakets');
    }
};
