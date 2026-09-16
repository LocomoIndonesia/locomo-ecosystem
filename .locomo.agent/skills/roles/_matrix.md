# Matriks Peran × Fase × Lajur (Tiket 3.1 — kanonis)

Peran di luar matriks ini TIDAK BOLEH diaktifkan. Aktivasi di luar sel fase/lajur = pelanggaran prosedur.

| Peran | THINK | SYNC | ACT | REFLECT | Lajur | Mandat tunggal |
|---|---|---|---|---|---|---|
| architect | ● | ○ deadlock desain | | | FACTORY, CRITICAL | Rancangan + FPLAN |
| uiux-developer | | ● | | | FACTORY, CRITICAL | Kontrak UI |
| dba | ○ review skema | ● | | | FACTORY, CRITICAL | Skema & migrasi (CRITICAL bila migrasi) |
| integrator | | ● | | | FACTORY, CRITICAL | Kontrak API/data antar komponen |
| coder | ○ VIBE solo | | ● | | VIBE, FACTORY, CRITICAL | Menulis kode (VIBE boleh solo langsung) |
| tester | | | ● | | FACTORY, CRITICAL | Test via registry perintah kanonis |
| reviewer | | | ● | | FACTORY, CRITICAL | Correctness pasca-coder |
| fixer | | | ● | | FACTORY, CRITICAL | Perbaiki temuan reviewer (maks 2 loop) |
| security-auditor | | | ● wajib di CRITICAL | | FACTORY opsional, CRITICAL wajib | Vuln, auth, secrets |
| performance-auditor | | | ○ budget | ● | FACTORY, CRITICAL | Budget kuantitatif (latensi, token, waktu query) — BUKAN correctness |
| synthesizer | | | | ● | FACTORY, CRITICAL | Kunci hasil + tulis learning-log |
| meta-controller | ○ stalemate >2 iterasi | ○ stalemate >2 iterasi | ○ stalemate >2 iterasi | | semua | Wasit deadlock saja; putus sekali, lalu kembali |
| explorer | ○ fallback Graphify gagal | | | | VIBE | Mata-telinga darurat; dilarang load JSON besar utuh |

● = peran utama fase itu. ○ = kondisional sesuai mandat.

Aturan:
1. VIBE = coder solo atau kerja langsung, tanpa swarm.
2. CRITICAL menambah security-auditor sebagai peran wajib.
3. Sengketa mandat antar-peran diputus meta-controller satu kali, lalu eksekusi kembali ke peran pemilik mandat.
4. `performance-auditor` vs `reviewer`: angka vs benar — tidak tumpang tindih.
