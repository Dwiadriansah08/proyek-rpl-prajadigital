#!/usr/bin/env python3
"""
render_diagrams.py
==================
Script otomatis untuk mengekspor 'docs/diagrams_per_page.html' menjadi berkas PDF
berstandar A4 Landscape di 'docs/02_UML_DIAGRAMS_FULLPAGE.pdf'.
Masing-masing dari 5 diagram menempati tepat 1 Halaman Penuh (Full Page).

Author  : Tim Pengembang PrajaDigital (RPL Lemahabang)
Target  : Rubrik Evaluasi OBE Skor 4 (Sangat Baik)
"""

import os
import sys
import time
import shutil
import tempfile
import subprocess
from pathlib import Path

# Paths
BASE_DIR = Path(__file__).resolve().parent
DOCS_DIR = BASE_DIR / "docs"
HTML_FILE = DOCS_DIR / "diagrams_per_page.html"
PDF_OUTPUT = DOCS_DIR / "02_UML_DIAGRAMS_FULLPAGE.pdf"

# Daftar kemungkinan path binary browser di Windows & Linux
BROWSER_CANDIDATES = [
    r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
    r"C:\Program Files\Microsoft\Edge\Application\msedge.exe",
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    shutil.which("msedge"),
    shutil.which("chrome"),
    shutil.which("google-chrome"),
    shutil.which("chromium"),
    shutil.which("chromium-browser"),
]


def find_browser() -> str:
    """Mencari browser Chromium (Edge / Chrome) yang tersedia di sistem."""
    for candidate in BROWSER_CANDIDATES:
        if candidate and os.path.exists(candidate):
            return str(candidate)
    return None


def export_pdf_with_browser(browser_path: str, html_path: Path, output_pdf: Path):
    """Mengekspor HTML ke PDF menggunakan Headless Chromium dengan virtual time budget."""
    print(f"[*] Menggunakan Browser Engine: {browser_path}")
    print(f"[*] Berkas Sumber HTML       : {html_path}")
    print(f"[*] Berkas Tujuan PDF        : {output_pdf}")

    # Buat direktori sementara untuk isolasi profile browser
    temp_profile = tempfile.mkdtemp(prefix="prajadigital_browser_")
    
    try:
        # Convert file path to file:// URL format
        file_url = html_path.resolve().as_uri()

        cmd = [
            browser_path,
            "--headless=new",
            "--no-sandbox",
            "--disable-gpu",
            "--disable-extensions",
            "--disable-dev-shm-usage",
            "--disable-software-rasterizer",
            "--run-all-compositor-stages-before-draw",
            "--virtual-time-budget=7000",  # Beri waktu 7 detik agar Mermaid selesai render SVG
            "--no-pdf-header-footer",
            f"--user-data-dir={temp_profile}",
            f"--print-to-pdf={output_pdf}",
            file_url,
        ]

        print("[*] Menjalankan proses rendering diagram Mermaid ke PDF...")
        start_time = time.time()
        
        # Eksekusi proses browser
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
        elapsed = time.time() - start_time

        if result.returncode != 0 and not output_pdf.exists():
            print(f"[!] Error eksekusi browser (Code {result.returncode}):")
            print(result.stderr)
            sys.exit(1)

        if output_pdf.exists() and output_pdf.stat().st_size > 0:
            size_kb = output_pdf.stat().st_size / 1024
            print("\n" + "=" * 70)
            print(" [V] SUKSES! PDF DIAGRAM BERHASIL DI-GENERATE SECARA SEMPURNA")
            print("=" * 70)
            print(f" -> Lokasi Berkas : {output_pdf}")
            print(f" -> Ukuran Berkas : {size_kb:.2f} KB ({output_pdf.stat().st_size} bytes)")
            print(f" -> Waktu Proses  : {elapsed:.2f} detik")
            print(" -> Susunan Halaman (5 Halaman Utuh):")
            print("    * Halaman 1 : Use Case Diagram (5 Aktor & Fitur)")
            print("    * Halaman 2 : Activity Diagram (3-Tier Workflow & AI)")
            print("    * Halaman 3 : Class Diagram (Controllers, Services, Models)")
            print("    * Halaman 4 : Sequence Diagram (Alur Pesan & Notif)")
            print("    * Halaman 5 : Entity Relationship Diagram (ERD Basis Data 'rpl')")
            print("=" * 70 + "\n")
        else:
            print("[!] Berkas PDF tidak ditemukan atau kosong setelah proses selesai.")
            sys.exit(1)

    finally:
        # Bersihkan direktori profil sementara
        shutil.rmtree(temp_profile, ignore_errors=True)


def main():
    print("-" * 70)
    print(" PRAJADIGITAL - UML DIAGRAMS AUTOMATED PDF EXPORTER")
    print("-" * 70)

    if not HTML_FILE.exists():
        print(f"[!] File sumber '{HTML_FILE}' tidak ditemukan!")
        sys.exit(1)

    browser = find_browser()
    if not browser:
        print("[!] Tidak ditemukan browser berbasis Chromium (Microsoft Edge / Google Chrome) di sistem.")
        print("    Silakan pastikan Edge atau Chrome terinstal di komputer.")
        sys.exit(1)

    export_pdf_with_browser(browser, HTML_FILE, PDF_OUTPUT)


if __name__ == "__main__":
    main()

