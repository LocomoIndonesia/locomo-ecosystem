#!/bin/bash
# ==============================================================================
# LOCOMO AGENT OS V2.0 - POST-EXECUTION EVALUATION HOOK TEMPLATE
# ==============================================================================
# Skrip ini memverifikasi integritas kode pasca-penulisan oleh Coder:
# 1. Melakukan static analysis singkat terhadap file baru/berubah.
# 2. Memeriksa ketiadaan kata kunci terlarang (Guardrails) dari id.json.
# 3. Menjalankan rangkaian unit test otomatis.
# ==============================================================================

echo "=== [START] Post-Execution Evaluation Hooks ==="

# 1. Pengecekan Guardrails (Ketiadaan Fungsi Terlarang)
echo "[INFO] Menjalankan pengecekan guardrails..."
FORBIDDEN_FUNCTIONS=("eval" "exec" "passthru" "shell_exec" "system")

# Contoh memindai kode di repositori utama untuk mencari fungsi terlarang
# (Dalam eksekusi nyata, skrip ini memindai file PHP yang baru dimodifikasi)
VIOLATIONS=0
for func in "${FORBIDDEN_FUNCTIONS[@]}"; do
    # Cari pola fungsi dalam file .php di repositori utama (tidak termasuk .antigravity / locomo.agent)
    # Gunakan grep untuk memindai
    matches=$(grep -rnw --include="*.php" "$func" . 2>/dev/null | grep -v ".antigravity" | grep -v "locomo.agent")
    if [ ! -z "$matches" ]; then
        echo -e "\e[31m[ERROR] Pelanggaran Guardrail ditemukan! Fungsi '$func' dilarang:\e[0m"
        echo "$matches"
        VIOLATIONS=$((VIOLATIONS + 1))
    fi
done

if [ $VIOLATIONS -gt 0 ]; then
    echo -e "\e[31m=== [FAILED] Evaluasi Gagal: Pelanggaran Guardrail Keamanan! ===\e[0m"
    exit 1
fi
echo "[PASS] Pengecekan guardrails sukses (tidak ada pelanggaran)."

# 2. Menjalankan Unit Test Terkait
echo "[INFO] Menjalankan automated test suite..."
# Silakan pasang perintah runner test sesuai dengan tumpukan teknologi Anda (Misal: vendor/bin/phpunit atau npm test)
# Contoh fallback simulasi:
if [ -f "vendor/bin/phpunit" ]; then
    vendor/bin/phpunit
    TEST_EXIT_CODE=$?
elif [ -f "package.json" ] && grep -q "test" package.json; then
    npm test
    TEST_EXIT_CODE=$?
else
    echo "[WARNING] Tidak ada runner pengujian otomatis yang terdeteksi di root proyek. Skip test run."
    TEST_EXIT_CODE=0
fi

if [ $TEST_EXIT_CODE -ne 0 ]; then
    echo -e "\e[31m=== [FAILED] Evaluasi Gagal: Unit Test tidak lolos! ===\e[0m"
    exit $TEST_EXIT_CODE
fi

echo -e "\e[32m=== [SUCCESS] Seluruh Evaluasi Lulus (DoD Terpenuhi) ===\e[0m"
exit 0
