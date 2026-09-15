# CREATED BY ibot VIA openclaw | LOCOMO SIGNATURE @2026 | 2026-09-15T19:00:00+07:00
#!/bin/bash
# ==============================================================================
# LOCOMO AGENT OS - POST-EXECUTION EVALUATION HOOK (Tiket 0.3)
# ==============================================================================
# Menegakkan Definition of Done secara JUJUR:
#   1. Guardrail scan HANYA pada file berubah (bukan seluruh repo).
#   2. Signature Locomo wajib pada file baru/ubah di luar scratch (Amandemen A3).
#   3. Test HARUS jalan di container (Docker-Only). Tanpa runner = gagal, bukan lolos.
#
# Pemakaian:
#   ./post_execution_eval.sh [file ...]          # scan file eksplisit, atau
#   ./post_execution_eval.sh                     # scan `git diff --name-only HEAD`
# Konfigurasi test via environment:
#   HOOK_PROJECT_DIR  direktori project (tempat docker compose / sail berada)
#   HOOK_TEST_CMD     perintah test lengkap, mis. "./vendor/bin/sail artisan test"
#
# Exit code: 0 PASS | 1 GUARDRAIL/SIGNATURE | 2 NO_RUNNER | 3 TEST_FAIL
# ==============================================================================

set -u

EXCLUDE_RE='(^|/)(vendor|node_modules|\.git|\.antigravity|\.locomo\.agent)/'
SCRATCH_RE='(^|/)scratch/'

# --- Kumpulkan daftar file ---
if [ "$#" -gt 0 ]; then
    FILES="$*"
else
    if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        FILES=$(git diff --name-only HEAD 2>/dev/null)
    else
        echo "[ERROR] Bukan repo git dan tidak ada file eksplisit. Berikan daftar file."
        exit 2
    fi
fi

# --- Filter: hanya file yang ada & relevan, kecualikan direktori sistem ---
TARGETS=""
for f in $FILES; do
    if [ ! -f "$f" ]; then
        continue
    fi
    if echo "$f" | grep -Eq "$EXCLUDE_RE"; then
        continue
    fi
    case "$f" in
        *.php|*.js|*.ts|*.py|*.sh) TARGETS="$TARGETS $f" ;;
    esac
done

if [ -z "$TARGETS" ]; then
    echo "[INFO] Tidak ada file kode yang berubah. Lanjut ke tahap test."
fi

# --- 1. Guardrail scan: fungsi terlarang pada file berubah ---
echo "[INFO] Guardrail scan pada file berubah..."
FORBIDDEN="eval|exec|passthru|shell_exec|system"
VIOLATIONS=0
for f in $TARGETS; do
    case "$f" in
        *.php)
            if echo "$f" | grep -Eq "$SCRATCH_RE"; then
                continue  # scratch = sandbox, bebas bereksperimen
            fi
            hits=$(grep -nwE "\b($FORBIDDEN)\s*\(" "$f" 2>/dev/null || true)
            if [ -n "$hits" ]; then
                echo "[ERROR] Guardrail: fungsi terlarang di $f:"
                echo "$hits"
                VIOLATIONS=$((VIOLATIONS + 1))
            fi
            ;;
    esac
done
if [ "$VIOLATIONS" -gt 0 ]; then
    echo "[FAILED] Guardrail violation. Exit 1."
    exit 1
fi
echo "[PASS] Guardrail bersih."

# --- 2. Signature check (A3): file baru/ubah di luar scratch wajib ber-header ---
echo "[INFO] Signature check..."
MISSING_SIG=0
for f in $TARGETS; do
    if echo "$f" | grep -Eq "$SCRATCH_RE"; then
        continue
    fi
    if ! head -n 5 "$f" 2>/dev/null | grep -q "LOCOMO SIGNATURE"; then
        echo "[ERROR] Signature hilang: $f (tambahkan header CREATED BY ... VIA ... | LOCOMO SIGNATURE ...)"
        MISSING_SIG=$((MISSING_SIG + 1))
    fi
done
if [ "$MISSING_SIG" -gt 0 ]; then
    echo "[FAILED] Signature violation. Exit 1."
    exit 1
fi
echo "[PASS] Signature lengkap."

# --- 3. Test via container (Docker-Only). Tanpa runner = GAGAL, bukan lolos ---
echo "[INFO] Menjalankan test suite..."
if [ -z "${HOOK_PROJECT_DIR:-}" ] || [ -z "${HOOK_TEST_CMD:-}" ]; then
    echo "[FAILED] NO_RUNNER: set HOOK_PROJECT_DIR dan HOOK_TEST_CMD. Contoh:"
    echo '  HOOK_PROJECT_DIR=/home/dev360_03/locomo.id/piticoin.id HOOK_TEST_CMD="./vendor/bin/sail artisan test"'
    exit 2
fi
if [ ! -d "$HOOK_PROJECT_DIR" ]; then
    echo "[FAILED] NO_RUNNER: direktori $HOOK_PROJECT_DIR tidak ada."
    exit 2
fi
(cd "$HOOK_PROJECT_DIR" && sh -c "$HOOK_TEST_CMD")
TEST_EXIT_CODE=$?
if [ "$TEST_EXIT_CODE" -ne 0 ]; then
    echo "[FAILED] Test gagal (exit $TEST_EXIT_CODE). Exit 3."
    exit 3
fi

echo "[SUCCESS] Seluruh evaluasi lulus (DoD terpenuhi). Exit 0."
exit 0
