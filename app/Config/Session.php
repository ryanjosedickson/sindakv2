<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\DatabaseHandler;

class Session extends BaseConfig
{
    /**
     * Pakai database handler, bukan file — supaya Super Admin bisa
     * paksa-invalidate session Operator Sekolah/Kampus tertentu langsung
     * lewat query, tanpa perlu akses filesystem server.
     */
    public string $driver = DatabaseHandler::class;

    /**
     * Nama cookie session dibuat khas ("sindak_v2_session"), BUKAN default
     * "ci_session". Ini penting karena SINDAK lama (port 8081) dan SINDAK
     * baru bisa jalan berdampingan di localhost selama masa transisi —
     * kalau nama cookie sama, sesi keduanya bisa saling menimpa/bentrok
     * di browser yang sama.
     */
    public string $cookieName = 'sindak_v2_session';

    /**
     * Masa aktif session dalam detik. 7200 = 2 jam tanpa aktivitas.
     * Operator biasanya kerja input data dalam sesi cukup panjang,
     * jadi 2 jam cukup wajar — bisa disesuaikan kalau kerasa kurang/lebih.
     */
    public int $expiration = 7200;

    /**
     * Untuk database handler, savePath diisi NAMA TABEL, bukan path folder.
     */
    public string $savePath = 'ci_sessions';

    /**
     * false = sesuai migration yang sudah dibuat (default saat generate).
     * Kalau nanti diubah ke true, migration ci_sessions perlu di-generate
     * ulang (primary key-nya beda), jadi JANGAN diubah sendiri tanpa
     * regenerate migration.
     */
    public bool $matchIP = false;

    /**
     * Regenerasi session ID setiap 5 menit (300 detik) untuk mitigasi
     * session fixation, tanpa terlalu sering mengganggu request AJAX
     * berurutan (konsisten dengan keputusan CSRF Opsi B sebelumnya).
     */
    public int $timeToUpdate = 300;

    /**
     * false: saat regenerasi ID, data session lama TIDAK langsung dihapus
     * (ada race-condition window singkat). Ini yang direkomendasikan CI4
     * untuk kompatibilitas request AJAX yang mungkin terjadi hampir
     * bersamaan — sejalan dengan alasan CSRF Opsi B.
     */
    public bool $regenerateDestroy = false;

    /**
     * null = pakai default database group (yang sudah dikonfigurasi ke
     * db-sindak di .env). Diisi eksplisit hanya kalau nanti session mau
     * disimpan di database/group terpisah dari data utama.
     */
    public ?string $DBGroup = null;
}
