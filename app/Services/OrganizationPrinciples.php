<?php

namespace App\Services;

/**
 * 7 Prinsip Dasar Gerakan Palang Merah & Bulan Sabit Merah Internasional.
 *
 * Redaksi default mengikuti rumusan resmi PMI. Admin dapat mengubah
 * daftar ini lewat Pengaturan → Profil Organisasi (tersimpan sebagai
 * JSON di Setting `org_principles`); bila kosong/rusak, tampilkan default.
 */
final class OrganizationPrinciples
{
    /**
     * @return array<int, array{title: string, body: string}>
     */
    public static function defaults(): array
    {
        return [
            [
                'title' => 'Kemanusiaan',
                'body' => 'Memberi pertolongan tanpa membeda-bedakan serta berupaya mencegah dan meringankan penderitaan sesama manusia.',
            ],
            [
                'title' => 'Kesamaan',
                'body' => 'Memberi pertolongan kepada siapa pun yang membutuhkan tanpa membedakan kebangsaan, ras, agama atau keyakinan, kelas sosial, dan pandangan politik.',
            ],
            [
                'title' => 'Kenetralan',
                'body' => 'Tidak memihak dan tidak melibatkan diri dalam pertentangan politik, ras, agama, maupun ideologi.',
            ],
            [
                'title' => 'Kemandirian',
                'body' => 'Bersifat mandiri; Perhimpunan Nasional tetap menjaga otonominya dalam menjalankan tugas kemanusiaan.',
            ],
            [
                'title' => 'Kesukarelaan',
                'body' => 'Bersifat sukarela dan tidak didorong oleh keinginan untuk mencari keuntungan materiil.',
            ],
            [
                'title' => 'Kesatuan',
                'body' => 'Hanya ada satu Perhimpunan Palang Merah atau Bulan Sabit Merah di setiap negara, terbuka bagi semua orang.',
            ],
            [
                'title' => 'Kesemestaan',
                'body' => 'Bersifat semesta; seluruh Perhimpunan memiliki kedudukan yang sama serta saling membantu satu sama lain.',
            ],
        ];
    }

    /**
     * Normalisasi nilai tersimpan (JSON string / array / null) menjadi
     * list yang valid; fallback ke default bila kosong.
     *
     * @return array<int, array{title: string, body: string}>
     */
    public static function resolve(mixed $stored): array
    {
        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored)) {
            return self::defaults();
        }

        $items = [];
        foreach (array_values($stored) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $body = trim((string) ($row['body'] ?? ''));
            if ($title === '' || $body === '') {
                continue;
            }
            $items[] = ['title' => $title, 'body' => $body];
        }

        return $items === [] ? self::defaults() : $items;
    }
}
