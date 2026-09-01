# Panduan Publish Workspace locomo.id ke Git Remote

Dokumen ini memuat panduan langkah demi langkah untuk mempublikasikan workspace federasi **locomo.id** ke Git (GitHub/GitLab/Gitea/Bitbucket), baik secara otomatis dengan bantuan agen maupun mandiri secara manual.

---

## 1. Pertimbangan Keamanan Arsitektur (Wajib Diperhatikan)

Workspace `locomo.id` merupakan ekosistem multi-proyek (federated monorepo) yang berisi:
- File konfigurasi lingkungan (`.env`, `.env.*`)
- Kunci API, credential database PostgreSQL, secret JWT Mandeh.ID
- Dependency besar (`vendor/`, `node_modules/`, `__pycache__/`, cache Docker/Ollama)
- Log dan temporary cache framework

> [!WARNING]
> Jangan pernah menjalankan `git add .` tanpa berkas `.gitignore` yang tepat di root workspace. File kredensial dan secret dapat terekspos ke publik jika tidak difilter.

---

## 2. Kenapa Agen Tidak Bisa Membuat Repo Remote Secara Penuh Otomatis?

1. **Autentikasi & Otorisasi**: Agen tidak memiliki akses kredensial (GitHub Personal Access Token / SSH Key / login akun) untuk membuat repo di akun Git remote Anda.
2. **Ketiadaan GitHub CLI (`gh`)**: Tool `gh` belum terpasang atau terautentikasi di lingkungan host saat ini.

---

## 3. Langkah-Langkah Publikasi Manual / Semi-Otomatis

### Langkah 1: Buat Repository Kosong di Layanan Git Remote
1. Masuk ke [GitHub](https://github.com/new) (atau GitLab/Bitbucket).
2. Buat repository baru (contoh nama: `locomo-federated-ecosystem` atau `locomo.id`).
3. Disarankan memilih visibility **Private** terlebih dahulu demi keamanan.
4. Jangan centang opsi "Add a README file" atau "Add .gitignore" (karena kita akan push repository lokal yang sudah ada).
5. Salin URL repository (contoh: `git@github.com:username/locomo.id.git` atau `https://github.com/username/locomo.id.git`).

---

### Langkah 2: Setup `.gitignore` Root (Bisa Dibuatkan Otomatis oleh Agen)
Berkas `.gitignore` di root harus memfilter:
```gitignore
# Environment & Secrets
.env
.env.*
!.env.example
*.pem
*.key
*.cert

# Dependencies
**/node_modules/
**/vendor/
**/__pycache__/
*.pyc

# Framework caches & logs
**/storage/logs/
**/storage/framework/cache/
**/storage/framework/sessions/
**/storage/framework/views/
.phpunit.cache/

# IDE & OS files
.DS_Store
Thumbs.db
.vscode/
.idea/

# Database dumps / persistent volumes if mapped locally
*.sqlite
*.sql
```

---

### Langkah 3: Konfigurasi Identitas Git Lokal
Jalankan di terminal host (jika belum diset):
```bash
git config --global user.name "Nama Anda"
git config --global user.email "email_anda@example.com"
```

---

### Langkah 4: Inisialisasi & Commit Awal
```bash
cd /home/dev360_03/locomo.id
git init -b main
git add .
git commit -m "feat: initial commit federated ecosystem locomo.id"
```

---

### Langkah 5: Hubungkan ke Remote dan Push
```bash
git remote add origin <URL_REPO_ANDA>
git branch -M main
git push -u origin main
```

---

## 4. Opsi Alternatif: Repositori Terpisah (Per-Project) vs Monorepo

| Model | Kelebihan | Kekurangan |
| :--- | :--- | :--- |
| **Monorepo (Root `locomo.id`)** | Satu kontrol versi untuk seluruh orkestrasi, `compose.yaml`, dan dokumen cross-project. | Ukuran repo lebih besar, izin akses git sama untuk semua sub-project. |
| **Multi-Repo (Per-Subfolder)** | Isolasi ketat (misal `unimart.group` dan `donasi.mandeh.online` punya repo masing-masing). | Perlu setup git submodule / repositori terpisah di tiap folder. |
