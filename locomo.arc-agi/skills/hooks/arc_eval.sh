#!/bin/bash
# ==============================================================================
# LOCOMO ARC-AGI — POST-EXECUTION EVALUATION HOOK (ARC Edition)
# ==============================================================================
# Evaluasi pasca-eksekusi untuk script matriks ARC-AGI:
# 1. Verifikasi ketiadaan modul terlarang dalam script Coder.
# 2. Eksekusi matrix_sandbox.py terhadap soal ARC aktif.
# 3. Mengembalikan status PASS/FAIL ke event bus.
# ==============================================================================

echo "=== [START] ARC-AGI Post-Execution Evaluation ==="

SCRIPT_PATH="$1"
TASK_PATH="$2"

if [ -z "$SCRIPT_PATH" ] || [ -z "$TASK_PATH" ]; then
    echo -e "\e[31m[ERROR] Usage: arc_eval.sh <script.py> <task.json>\e[0m"
    exit 1
fi

# 1. Pengecekan Guardrails Keamanan (Modul Terlarang)
echo "[INFO] Scanning script for forbidden modules..."
FORBIDDEN=("os.system" "subprocess" "shutil.rmtree" "eval(" "exec(")
VIOLATIONS=0

for pattern in "${FORBIDDEN[@]}"; do
    if grep -q "$pattern" "$SCRIPT_PATH"; then
        echo -e "\e[31m[ERROR] Forbidden module/function detected: '$pattern'\e[0m"
        VIOLATIONS=$((VIOLATIONS + 1))
    fi
done

if [ $VIOLATIONS -gt 0 ]; then
    echo -e "\e[31m=== [FAILED] Guardrail violation: $VIOLATIONS forbidden patterns found ===\e[0m"
    exit 1
fi
echo "[PASS] Guardrail check passed (no forbidden modules)."

# 2. Eksekusi Matrix Sandbox
echo "[INFO] Executing matrix sandbox validation..."
python scratch/matrix_sandbox.py "$SCRIPT_PATH" "$TASK_PATH"
SANDBOX_EXIT=$?

if [ $SANDBOX_EXIT -ne 0 ]; then
    echo -e "\e[31m=== [FAILED] Matrix validation failed (exit code: $SANDBOX_EXIT) ===\e[0m"
    exit $SANDBOX_EXIT
fi

echo -e "\e[32m=== [SUCCESS] All ARC evaluations passed ===\e[0m"
exit 0
