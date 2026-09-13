<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HelpCenterController extends Controller
{
    /**
     * Display the Help Center & FAQ Hub.
     */
    public function index(Request $request): Response
    {
        $faqs = [
            [
                'category' => 'Perburuan Harta Karun & Staking',
                'items' => [
                    [
                        'q' => 'Apa perbedaan antara Koin Perak dan Koin Emas?',
                        'a' => 'Koin Perak (Silver) adalah utility token simulasi yang digunakan sebagai jaminan (staking) saat mengambil misi ekspedisi Gunung Mas. Koin Perak tidak dapat ditarik langsung ke rekening bank. Sebaliknya, Koin Emas (Gold) adalah reward poin hasil penyelesaian misi yang memiliki nilai riil dan dapat dicairkan (withdrawable) ke rekening bank atau digunakan untuk membayar langganan layanan ekosistem Locomo.',
                    ],
                    [
                        'q' => 'Bagaimana mekanisme Escrow Staking bekerja?',
                        'a' => 'Saat Anda mengambil misi, sejumlah Koin Perak akan dikunci di smart escrow. Jika misi berhasil diverifikasi dalam batas waktu, Koin Perak Anda akan dikembalikan 100% utuh ditambah hadiah Koin Emas. Jika misi kedaluwarsa atau gagal, Koin Perak hangus sesuai algoritma tanpa ganti rugi.',
                    ],
                    [
                        'q' => 'Apa itu Mikro Gas Fee 5%?',
                        'a' => 'Setiap transaksi pencairan reward atau penukaran koin dikenakan kontribusi mikro 5% yang dialokasikan secara transparan: 2.5% masuk ke Cadangan Likuiditas Bersama (Liquidity Pool) dan 2.5% didistribusikan ke Karavan Upline Saudagar Pembina Anda.',
                    ],
                ],
            ],
            [
                'category' => 'Ekspansi Jaringan & Karavan',
                'items' => [
                    [
                        'q' => 'Bagaimana cara membangun jaringan Karavan saya?',
                        'a' => 'Salin kode atau link referral dari dashboard utama Anda dan bagikan kepada rekan Anda. Setiap anggota baru yang bergabung menggunakan link Anda akan otomatis tercatat sebagai Downline Level 1, dan saat mereka mengajak orang lain, akan tercatat sebagai Downline Level 2.',
                    ],
                    [
                        'q' => 'Bagaimana cara mendaftarkan merchant warung atau UMKM baru?',
                        'a' => 'Buka menu Ekspansi Bisnis di dashboard PitiCoin, masukkan nama toko, pemilik, dan koordinat GPS. Sistem akan secara otomatis mengunci referral Anda ke sistem Unimart.group sehingga Anda memperoleh komisi transaksi dari warung tersebut selamanya.',
                    ],
                    [
                        'q' => 'Berapa besaran komisi pasif dari karavan downline?',
                        'a' => 'Anda menerima 10% dari setiap starter kit yang dibeli oleh Downline Level 1 dan 5% dari Downline Level 2, serta bagian dari gas fee setiap misi sukses yang mereka selesaikan.',
                    ],
                ],
            ],
            [
                'category' => 'Dompet, Deposit & Pencairan',
                'items' => [
                    [
                        'q' => 'Bagaimana cara mencairkan Koin Emas ke Rupiah?',
                        'a' => 'Pastikan saldo Koin Emas Anda mencapai batas minimum (Rp 50.000). Klik tombol "Tarik Saldo" pada kartu dompet emas di Dashboard, pilih rekening bank tujuan atau e-wallet terdaftar, lalu konfirmasi penarikan.',
                    ],
                    [
                        'q' => 'Berapa lama proses pencairan berlangsung?',
                        'a' => 'Pencairan diproses secara semi-otomatis melalui gateway perbankan nasional dalam waktu 5 hingga 30 menit pada jam kerja perbankan.',
                    ],
                    [
                        'q' => 'Apa yang dimaksud dengan Cadangan Likuiditas (Liquidity Pool)?',
                        'a' => 'Cadangan Likuiditas adalah dana kas riil (Fiat) yang disimpan dalam escrow bank penjamin untuk memastikan bahwa setiap 1 Koin Emas yang beredar dijamin 100% oleh cadangan kas nyata.',
                    ],
                ],
            ],
            [
                'category' => 'Akun, Lisensi & Keamanan',
                'items' => [
                    [
                        'q' => 'Bagaimana cara upgrade dari Free Tier ke Merchant Tier?',
                        'a' => 'Anda dapat membeli Starter Kit Lisensi Saudagar seharga Rp 100.000 melalui menu Upgrade di Dashboard. Pembelian ini langsung menginjeksi 100.000 Koin Perak dan membuka hak staking quest bernilai tinggi.',
                    ],
                    [
                        'q' => 'Bagaimana jika perangkat login saya dicurigai dibajak?',
                        'a' => 'Buka halaman Profil > Keamanan Sesi, periksa daftar perangkat yang sedang login. Anda dapat langsung menekan tombol "Keluarkan dari Semua Perangkat Lain" dengan memasukkan kata sandi akun Anda untuk memutuskan akses asing secara instan.',
                    ],
                    [
                        'q' => 'Apakah satu orang boleh memiliki banyak akun (Multi-Akun)?',
                        'a' => 'Dilarang keras. Sistem menerapkan proteksi Anti-Sybil. Akun ganda dengan identitas tiruan untuk manipulasi komisi akan dibekukan secara permanen oleh Admin Root Level 0.',
                    ],
                ],
            ],
        ];

        return Inertia::render('Help/Index', [
            'faqs' => $faqs,
            'officialWhatsApp' => '6281234567890',
            'supportEmail' => 'bantuan@piticoin.id',
        ]);
    }
}
