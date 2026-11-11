#!/usr/bin/env python3
"""
build_final_report_pdf.py
=========================
Renders docs/FINAL_REPORT_PRAJADIGITAL.md into an official academic PDF:
docs/00_FINAL_LAPORAN_UTAMA_PRAJADIGITAL.pdf

Layout requirements:
- Margins: Left 4cm, Top 4cm, Right 3cm, Bottom 3cm
- Chapter page breaks: Every BAB starts on a fresh page
- Full-page diagrams: 5 UML/ERD diagrams in Chapter 3 each occupy a full clean page
- Professional academic typography, tables, and code snippets
"""

import os
import re
import base64
import tempfile
import subprocess
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
DOCS_DIR = BASE_DIR / "docs"
MD_FILE = DOCS_DIR / "FINAL_REPORT_PRAJADIGITAL.md"
PDF_FILE = DOCS_DIR / "00_FINAL_LAPORAN_UTAMA_PRAJADIGITAL.pdf"
DIAGRAMS_DIR = DOCS_DIR / "diagrams"

BROWSER_PATHS = [
    r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
    r"C:\Program Files\Microsoft\Edge\Application\msedge.exe",
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
]

def find_browser():
    for p in BROWSER_PATHS:
        if os.path.exists(p):
            return p
    return None

def get_image_base64(img_path):
    if not os.path.exists(img_path):
        return ""
    with open(img_path, "rb") as f:
        data = base64.b64encode(f.read()).decode("utf-8")
    ext = os.path.splitext(img_path)[1].lower().replace(".", "")
    mime = "image/png" if ext == "png" else f"image/{ext}"
    return f"data:{mime};base64,{data}"

def parse_markdown(md_content):
    """Simple parser converting markdown content into rich semantic HTML."""
    lines = md_content.splitlines()
    html_out = []
    in_code = False
    code_lang = ""
    code_lines = []
    in_table = False
    table_rows = []
    in_list = False

    def close_table():
        nonlocal in_table, table_rows
        if not in_table:
            return ""
        in_table = False
        out = ['<table class="academic-table">']
        for idx, row in enumerate(table_rows):
            cells = [c.strip() for c in row.strip("|").split("|")]
            # Skip separator line (e.g. |:---|:---|)
            if idx == 1 and all(set(c).issubset({'-', ':', ' '}) for c in cells):
                continue
            tag = "th" if idx == 0 else "td"
            row_html = "<tr>" + "".join(f"<{tag}>{inline_format(c)}</{tag}>" for c in cells) + "</tr>"
            out.append(row_html)
        out.append("</table>")
        table_rows = []
        return "\n".join(out)

    def close_list():
        nonlocal in_list
        if in_list:
            in_list = False
            return "</ul>"
        return ""

    def inline_format(text):
        # Escape html entities
        text = text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
        # Bold + Italic
        text = re.sub(r'\*\*\*(.*?)\*\*\*', r'<strong><em>\1</em></strong>', text)
        # Bold
        text = re.sub(r'\*\*(.*?)\*\*', r'<strong>\1</strong>', text)
        # Italic
        text = re.sub(r'\*(.*?)\*', r'<em>\1</em>', text)
        # Inline code
        text = re.sub(r'`(.*?)`', r'<code>\1</code>', text)
        # Links
        text = re.sub(r'\[(.*?)\]\((.*?)\)', r'<a href="\2">\1</a>', text)
        # Math LaTeX approximations: $\le$ to &le;, etc.
        text = text.replace(r'$\le 3$', '&le; 3').replace(r'$\le$', '&le;')
        text = text.replace(r'$1:1$', '1:1').replace(r'$1:N$', '1:N')
        text = text.replace(r'$100\%$', '100%').replace(r'$99.5\%$', '99.5%')
        text = text.replace(r'$< 800\text{ ms}$', '&lt; 800 ms').replace(r'$1.5 - 3.0\text{ detik}$', '1.5 - 3.0 detik')
        return text

    i = 0
    total_lines = len(lines)
    while i < total_lines:
        line = lines[i]

        # Code block
        if line.strip().startswith("```"):
            if in_code:
                in_code = False
                code_text = "\n".join(code_lines)
                html_out.append(f'<pre><code class="language-{code_lang}">{code_text}</code></pre>')
                code_lines = []
            else:
                html_out.append(close_table())
                html_out.append(close_list())
                in_code = True
                code_lang = line.strip()[3:].strip()
            i += 1
            continue

        if in_code:
            # Escape HTML in code block
            escaped_code = line.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            code_lines.append(escaped_code)
            i += 1
            continue

        # Tables
        if line.strip().startswith("|") and line.strip().endswith("|"):
            html_out.append(close_list())
            in_table = True
            table_rows.append(line.strip())
            i += 1
            continue
        elif in_table:
            html_out.append(close_table())

        # Unordered list
        if line.strip().startswith(("* ", "- ")):
            html_out.append(close_table())
            if not in_list:
                in_list = True
                html_out.append("<ul>")
            item_text = line.strip()[2:]
            html_out.append(f"<li>{inline_format(item_text)}</li>")
            i += 1
            continue
        else:
            if in_list and not line.strip().startswith(("* ", "- ")):
                html_out.append(close_list())

        # Horizontal rule
        if line.strip() in ("---", "***", "___"):
            html_out.append('<hr class="divider"/>')
            i += 1
            continue

        # Blockquote
        if line.strip().startswith(">"):
            html_out.append(close_table())
            html_out.append(close_list())
            quote_lines = []
            while i < total_lines and lines[i].strip().startswith(">"):
                quote_lines.append(lines[i].strip()[1:].strip())
                i += 1
            quote_body = "<br/>".join(inline_format(q) for q in quote_lines if q)
            html_out.append(f"<blockquote>{quote_body}</blockquote>")
            continue

        # Headings
        if line.startswith("# "):
            title = inline_format(line[2:].strip())
            html_out.append(f'<h1 class="chapter-title">{title}</h1>')
            i += 1
            continue
        if line.startswith("## "):
            title = inline_format(line[3:].strip())
            html_out.append(f'<h2 class="sub-chapter-title">{title}</h2>')
            i += 1
            continue
        if line.startswith("### "):
            title = inline_format(line[4:].strip())
            html_out.append(f'<h3>{title}</h3>')
            i += 1
            continue
        if line.startswith("#### "):
            title = inline_format(line[5:].strip())
            html_out.append(f'<h4>{title}</h4>')
            i += 1
            continue

        # Images (special handling for the 5 full page diagrams)
        img_match = re.match(r'!\[(.*?)\]\((.*?)\)', line.strip())
        if img_match:
            caption = img_match.group(1)
            img_rel_path = img_match.group(2)
            # Resolve relative image path
            img_full_path = (DOCS_DIR / img_rel_path).resolve()
            img_b64 = get_image_base64(str(img_full_path))
            html_out.append(f'''
            <div class="diagram-fullpage-container">
                <div class="diagram-inner">
                    <img src="{img_b64}" alt="{caption}" class="diagram-image"/>
                    <p class="diagram-caption"><strong>{caption}</strong></p>
                </div>
            </div>
            ''')
            i += 1
            continue

        # Empty lines
        if not line.strip():
            i += 1
            continue

        # Normal paragraph
        html_out.append(f"<p>{inline_format(line.strip())}</p>")
        i += 1

    html_out.append(close_table())
    html_out.append(close_list())
    return "\n".join(html_out)

def build_html_document(body_html):
    return f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Akhir Praktikum RPL - PrajaDigital Desa Lemahabang</title>
<style>
    @page {{
        size: A4 portrait;
        margin-top: 4cm;
        margin-left: 4cm;
        margin-right: 3cm;
        margin-bottom: 3cm;
        @bottom-right {{
            content: counter(page);
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
        }}
    }}

    * {{
        box-sizing: border-box;
    }}

    body {{
        font-family: 'Times New Roman', 'Liberation Serif', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        color: #111827;
        text-align: justify;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
    }}

    /* Heading Styles */
    h1.chapter-title {{
        font-size: 16pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        color: #0f172a;
        page-break-before: always;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 0.5rem;
    }}

    /* First chapter doesn't break if preceded by title cover */
    body > h1:first-of-type {{
        page-break-before: avoid;
    }}

    h2.sub-chapter-title {{
        font-size: 13pt;
        font-weight: bold;
        margin-top: 1.4rem;
        margin-bottom: 0.8rem;
        color: #1e293b;
    }}

    h3 {{
        font-size: 12pt;
        font-weight: bold;
        margin-top: 1.2rem;
        margin-bottom: 0.6rem;
        color: #1e3a8a;
    }}

    h4 {{
        font-size: 11pt;
        font-weight: bold;
        font-style: italic;
        margin-top: 1rem;
        margin-bottom: 0.4rem;
        color: #334155;
    }}

    p {{
        margin-top: 0;
        margin-bottom: 0.8rem;
        text-indent: 1.27cm; /* Indentasi paragraf baku akademik */
    }}

    /* Lists without text-indent */
    ul, ol {{
        margin-top: 0;
        margin-bottom: 1rem;
        padding-left: 1.8cm;
    }}

    li {{
        margin-bottom: 0.4rem;
        text-indent: 0;
    }}

    /* Blockquotes */
    blockquote {{
        margin: 1rem 0;
        padding: 0.8rem 1.2rem;
        background-color: #f8fafc;
        border-left: 4px solid #2563eb;
        border-radius: 4px;
        font-size: 11pt;
        line-height: 1.5;
        color: #334155;
        text-indent: 0;
    }}

    /* Tables */
    table.academic-table {{
        width: 100%;
        border-collapse: collapse;
        margin: 1.2rem 0;
        font-size: 10pt;
        page-break-inside: avoid;
    }}

    table.academic-table th, table.academic-table td {{
        border: 1px solid #94a3b8;
        padding: 6px 10px;
        text-align: left;
        vertical-align: top;
    }}

    table.academic-table th {{
        background-color: #f1f5f9;
        font-weight: bold;
        color: #0f172a;
        text-align: center;
    }}

    table.academic-table tr:nth-child(even) {{
        background-color: #f8fafc;
    }}

    /* Code blocks */
    pre {{
        background-color: #0f172a;
        color: #f8fafc;
        padding: 10px 14px;
        border-radius: 6px;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 8.5pt;
        line-height: 1.4;
        overflow-x: auto;
        margin: 1rem 0;
        page-break-inside: avoid;
    }}

    code {{
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 9.5pt;
        background-color: #f1f5f9;
        color: #0f172a;
        padding: 1px 4px;
        border-radius: 3px;
    }}

    pre code {{
        background-color: transparent;
        color: inherit;
        padding: 0;
    }}

    hr.divider {{
        border: none;
        border-top: 1px solid #cbd5e1;
        margin: 1.5rem 0;
    }}

    /* Diagram Full-page display */
    .diagram-fullpage-container {{
        page-break-before: always;
        page-break-after: always;
        width: 100%;
        min-height: 82vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin: 0;
        padding: 0;
    }}

    .diagram-inner {{
        width: 100%;
        text-align: center;
    }}

    .diagram-image {{
        max-width: 98%;
        max-height: 72vh;
        height: auto;
        object-fit: contain;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin: 0 auto;
        display: block;
    }}

    .diagram-caption {{
        margin-top: 0.8rem;
        font-size: 10.5pt;
        font-style: italic;
        color: #1e293b;
        text-align: center;
        text-indent: 0;
    }}

    /* Print specific tweaks */
    @media print {{
        body {{
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }}
    }}
</style>
</head>
<body>
{body_html}
</body>
</html>
"""

def generate_pdf():
    print("[*] Membaca berkas markdown:", MD_FILE)
    if not MD_FILE.exists():
        print(f"[!] Berkas markdown tidak ditemukan di {MD_FILE}")
        return False

    with open(MD_FILE, "r", encoding="utf-8") as f:
        md_text = f.read()

    print("[*] Melakukan translasi Markdown ke HTML Terstruktur...")
    body_html = parse_markdown(md_text)
    full_html = build_html_document(body_html)

    temp_html_path = DOCS_DIR / "temp_final_report.html"
    with open(temp_html_path, "w", encoding="utf-8") as f:
        f.write(full_html)
    print(f"[*] Berkas HTML sementara tersimpan: {temp_html_path}")

    browser_bin = find_browser()
    if not browser_bin:
        print("[!] Tidak ditemukan binary Microsoft Edge / Google Chrome di sistem.")
        return False

    print(f"[*] Engine browser terdeteksi: {browser_bin}")
    temp_profile = tempfile.mkdtemp(prefix="prajadigital_pdf_")
    file_url = temp_html_path.resolve().as_uri()

    cmd = [
        browser_bin,
        "--headless=new",
        "--no-sandbox",
        "--disable-gpu",
        "--disable-extensions",
        "--disable-dev-shm-usage",
        "--disable-software-rasterizer",
        "--run-all-compositor-stages-before-draw",
        "--no-pdf-header-footer",
        f"--user-data-dir={temp_profile}",
        f"--print-to-pdf={PDF_FILE}",
        file_url,
    ]

    print("[*] Mengonversi ke PDF resmi dengan layout A4 & Full-Page Diagrams...")
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=90)

    # Cleanup temp html
    if temp_html_path.exists():
        try:
            os.remove(temp_html_path)
        except Exception:
            pass

    if PDF_FILE.exists() and PDF_FILE.stat().st_size > 0:
        size_kb = PDF_FILE.stat().st_size / 1024
        print("\n" + "=" * 70)
        print(" [V] SUKSES! DOKUMEN LAPORAN AKHIR RESMI BERHASIL DICETAK")
        print("=" * 70)
        print(f" -> Lokasi Berkas : {PDF_FILE}")
        print(f" -> Ukuran Berkas : {size_kb:.2f} KB ({PDF_FILE.stat().st_size} bytes)")
        return True
    else:
        print("[!] Gagal men-generate PDF:")
        print(result.stderr)
        return False

if __name__ == "__main__":
    success = generate_pdf()
    if not success:
        exit(1)

