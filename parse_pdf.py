import os
import sys
import pypdf

pdf_path = "/workspace/docs/sistem.operasi/Vanguard_HPI_Pessel_2026.pdf"

if not os.path.exists(pdf_path):
    print(f"File not found: {pdf_path}")
    sys.exit(1)

reader = pypdf.PdfReader(pdf_path)
num_pages = len(reader.pages)
print(f"Total pages: {num_pages}")

for idx, page in enumerate(reader.pages):
    text = page.extract_text()
    print(f"Page {idx+1}: text length = {len(text)}")
    if len(text) > 0:
        print(f"--- First 500 chars of Page {idx+1} ---")
        print(text[:500])
        print("--------------------------------------")
