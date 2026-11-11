"""
PrajaDigital UML Diagram Generator
Generates 5 UML/ERD diagrams as high-resolution PNG files using matplotlib.
Output: docs/diagrams/01_usecase.png through 05_erd.png
"""

import os
import sys
import math
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch
from matplotlib.lines import Line2D
import matplotlib.patheffects as pe

# ── Output directory ──────────────────────────────────────────────────────────
OUT_DIR = os.path.join(os.path.dirname(__file__), "docs", "diagrams")
os.makedirs(OUT_DIR, exist_ok=True)

# ── Colour palette ────────────────────────────────────────────────────────────
C = {
    "navy":    "#1a2b5e",
    "blue":    "#2563eb",
    "sky":     "#0ea5e9",
    "teal":    "#0d9488",
    "green":   "#16a34a",
    "orange":  "#ea580c",
    "red":     "#dc2626",
    "purple":  "#7c3aed",
    "gray":    "#6b7280",
    "light":   "#e0e7ff",
    "bg":      "#f8fafc",
    "white":   "#ffffff",
    "black":   "#0f172a",
    "gold":    "#d97706",
}

DPI = 150
A4_W, A4_H = 11.69, 8.27          # landscape A4 in inches

def savefig(fig, name):
    path = os.path.join(OUT_DIR, name)
    fig.savefig(path, dpi=DPI, bbox_inches='tight', facecolor=fig.get_facecolor())
    plt.close(fig)
    kb = os.path.getsize(path) // 1024
    print(f"  ✓ Saved {name}  ({kb} KB)")
    return path

def title_bar(ax, text, subtitle=""):
    ax.text(0.5, 0.97, text, transform=ax.transAxes,
            fontsize=20, fontweight='bold', color=C["navy"],
            ha='center', va='top',
            fontfamily='DejaVu Sans')
    if subtitle:
        ax.text(0.5, 0.925, subtitle, transform=ax.transAxes,
                fontsize=10, color=C["gray"], ha='center', va='top', style='italic')

# ─────────────────────────────────────────────────────────────────────────────
# DIAGRAM 1: USE CASE DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
def draw_actor(ax, x, y, label, color=C["navy"]):
    """Stick-figure actor."""
    # Head
    head = plt.Circle((x, y + 0.38), 0.07, color=color, zorder=5)
    ax.add_patch(head)
    # Body
    ax.plot([x, x], [y + 0.31, y + 0.10], color=color, lw=2, zorder=5)
    # Arms
    ax.plot([x - 0.11, x + 0.11], [y + 0.22, y + 0.22], color=color, lw=2, zorder=5)
    # Legs
    ax.plot([x, x - 0.09], [y + 0.10, y - 0.07], color=color, lw=2, zorder=5)
    ax.plot([x, x + 0.09], [y + 0.10, y - 0.07], color=color, lw=2, zorder=5)
    ax.text(x, y - 0.15, label, ha='center', va='top', fontsize=8.5,
            fontweight='bold', color=color, wrap=True)

def draw_usecase(ax, x, y, w, h, label, color=C["blue"], fontsize=8):
    ellipse = mpatches.Ellipse((x, y), w, h,
                                facecolor=color + "22", edgecolor=color,
                                linewidth=1.8, zorder=4)
    ax.add_patch(ellipse)
    ax.text(x, y, label, ha='center', va='center', fontsize=fontsize,
            color=C["black"], fontweight='semibold', wrap=True,
            multialignment='center')

def diagram_usecase():
    fig, ax = plt.subplots(figsize=(A4_W, A4_H), facecolor=C["bg"])
    ax.set_xlim(0, 12); ax.set_ylim(0, 8.5); ax.axis('off')
    ax.set_facecolor(C["bg"])

    # Title
    title_bar(ax, "USE CASE DIAGRAM — PrajaDigital",
              "Sistem Informasi & Pelayanan Publik Cerdas Desa Lemahabang")

    # System boundary box
    sys_rect = FancyBboxPatch((2.6, 0.5), 7.6, 7.2,
                               boxstyle="round,pad=0.05",
                               linewidth=2, edgecolor=C["navy"],
                               facecolor=C["white"], zorder=1, linestyle='--')
    ax.add_patch(sys_rect)
    ax.text(6.4, 7.6, "«system»  PrajaDigital Web Application",
            ha='center', va='center', fontsize=10, color=C["navy"],
            fontweight='bold', style='italic')

    # Actors (left side)
    actors_left = [
        (0.9, 6.4, "Warga",       C["teal"]),
        (0.9, 4.5, "RT (Ketua)",  C["blue"]),
        (0.9, 2.6, "RW (Ketua)",  C["purple"]),
    ]
    for x, y, lbl, col in actors_left:
        draw_actor(ax, x, y, lbl, col)

    # Actors (right side)
    actors_right = [
        (11.1, 5.5, "Admin Desa", C["orange"]),
        (11.1, 2.8, "Super Admin", C["red"]),
    ]
    for x, y, lbl, col in actors_right:
        draw_actor(ax, x, y, lbl, col)

    # Use Cases — column 1 (left inside)
    uc_left = [
        (4.4, 6.5, 2.4, 0.55, "UC-01\nAjukan Surat",         C["teal"]),
        (4.4, 5.5, 2.4, 0.55, "UC-02\nLihat Status Surat",   C["teal"]),
        (4.4, 4.5, 2.4, 0.55, "UC-03\nApproval RT",          C["blue"]),
        (4.4, 3.5, 2.4, 0.55, "UC-04\nApproval RW",          C["purple"]),
        (4.4, 2.5, 2.4, 0.55, "UC-05\nConsult AI Assistant", C["sky"]),
    ]
    for x, y, w, h, lbl, col in uc_left:
        draw_usecase(ax, x, y, w, h, lbl, col, fontsize=7.5)

    # Use Cases — column 2 (right inside)
    uc_right = [
        (8.2, 6.5, 2.4, 0.55, "UC-06\nApproval Admin Desa", C["orange"]),
        (8.2, 5.5, 2.4, 0.55, "UC-07\nGenerate PDF Surat",  C["orange"]),
        (8.2, 4.5, 2.4, 0.55, "UC-08\nSend Email & WA",     C["orange"]),
        (8.2, 3.5, 2.4, 0.55, "UC-09\nKelola Laporan",      C["orange"]),
        (8.2, 2.5, 2.4, 0.55, "UC-10\nKonfigurasi AI",      C["red"]),
        (8.2, 1.5, 2.4, 0.55, "UC-11\nManage Users/Roles",  C["red"]),
    ]
    for x, y, w, h, lbl, col in uc_right:
        draw_usecase(ax, x, y, w, h, lbl, col, fontsize=7.5)

    # Association lines
    def assoc(ax, x1, y1, x2, y2, col=C["gray"]):
        ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-", color=col, lw=1.3))

    # Warga → UC-01, 02, 05
    assoc(ax, 1.1, 6.6, 3.2, 6.5)
    assoc(ax, 1.1, 6.4, 3.2, 5.5, C["teal"])
    assoc(ax, 1.2, 6.2, 3.2, 2.5, C["sky"])

    # RT → UC-03
    assoc(ax, 1.1, 4.6, 3.2, 4.5)
    assoc(ax, 1.2, 4.4, 3.2, 2.5, C["sky"])

    # RW → UC-04
    assoc(ax, 1.1, 2.7, 3.2, 3.5)
    assoc(ax, 1.2, 2.5, 3.2, 2.5, C["sky"])

    # Admin Desa → UC-06..09
    assoc(ax, 10.9, 5.6, 9.4, 6.5)
    assoc(ax, 10.9, 5.5, 9.4, 5.5)
    assoc(ax, 10.9, 5.4, 9.4, 4.5)
    assoc(ax, 10.9, 5.3, 9.4, 3.5)

    # Super Admin → UC-10, 11
    assoc(ax, 10.9, 2.9, 9.4, 2.5)
    assoc(ax, 10.9, 2.7, 9.4, 1.5)

    # Extend / Include
    ax.annotate("", xy=(8.0, 6.5), xytext=(5.6, 6.5),
                arrowprops=dict(arrowstyle="->", color=C["gray"],
                                lw=1.2, linestyle='dashed'))
    ax.text(6.8, 6.65, "«include»", fontsize=6.5, color=C["gray"], ha='center')

    ax.annotate("", xy=(8.0, 5.5), xytext=(5.6, 5.5),
                arrowprops=dict(arrowstyle="->", color=C["gray"],
                                lw=1.2, linestyle='dashed'))
    ax.text(6.8, 5.65, "«include»", fontsize=6.5, color=C["gray"], ha='center')

    # Legend
    legend_items = [
        mpatches.Patch(facecolor=C["teal"]+"33", edgecolor=C["teal"], label="Warga"),
        mpatches.Patch(facecolor=C["blue"]+"33", edgecolor=C["blue"], label="RT"),
        mpatches.Patch(facecolor=C["purple"]+"33", edgecolor=C["purple"], label="RW"),
        mpatches.Patch(facecolor=C["orange"]+"33", edgecolor=C["orange"], label="Admin Desa"),
        mpatches.Patch(facecolor=C["red"]+"33", edgecolor=C["red"], label="Super Admin"),
    ]
    ax.legend(handles=legend_items, loc='lower right', fontsize=7.5,
              framealpha=0.85, ncol=5)

    fig.tight_layout(pad=0.3)
    return savefig(fig, "01_usecase.png")


# ─────────────────────────────────────────────────────────────────────────────
# DIAGRAM 2: ACTIVITY DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
def draw_rounded_box(ax, x, y, w, h, label, fcolor, ecolor, fontsize=8.5, lw=1.8):
    box = FancyBboxPatch((x - w/2, y - h/2), w, h,
                          boxstyle="round,pad=0.07",
                          facecolor=fcolor, edgecolor=ecolor,
                          linewidth=lw, zorder=4)
    ax.add_patch(box)
    ax.text(x, y, label, ha='center', va='center', fontsize=fontsize,
            color=C["black"], fontweight='semibold', multialignment='center', zorder=5)

def draw_diamond(ax, x, y, w, h, label, fcolor=C["gold"], fontsize=8):
    diamond = plt.Polygon(
        [[x, y+h/2], [x+w/2, y], [x, y-h/2], [x-w/2, y]],
        closed=True, facecolor=fcolor+"44", edgecolor=fcolor,
        linewidth=2, zorder=4)
    ax.add_patch(diamond)
    ax.text(x, y, label, ha='center', va='center', fontsize=fontsize,
            color=C["black"], fontweight='bold', zorder=5)

def arr(ax, x1, y1, x2, y2, label="", col=C["navy"], lw=1.5):
    ax.annotate("", xy=(x2, y2), xytext=(x1, y1),
                arrowprops=dict(arrowstyle="->", color=col,
                                lw=lw, connectionstyle="arc3,rad=0"))
    if label:
        mx, my = (x1+x2)/2, (y1+y2)/2
        ax.text(mx + 0.1, my, label, fontsize=7.5, color=col,
                ha='left', va='center', style='italic')

def diagram_activity():
    fig, ax = plt.subplots(figsize=(A4_W, A4_H), facecolor=C["bg"])
    ax.set_xlim(0, 12); ax.set_ylim(-0.5, 9); ax.axis('off')
    ax.set_facecolor(C["bg"])

    title_bar(ax, "ACTIVITY DIAGRAM — PrajaDigital",
              "Alur Bisnis Pengajuan Surat 3-Tier & AI Chatbot")

    # ─── LEFT LANE: Alur Pengajuan Surat ───
    lane_x = 3.5
    ax.add_patch(FancyBboxPatch((0.3, -0.3), 6.5, 8.6,
                                 boxstyle="round,pad=0.05",
                                 linewidth=1.5, edgecolor=C["gray"],
                                 facecolor="#eff6ff", zorder=0, alpha=0.6))
    ax.text(3.55, 8.2, "Alur Pengajuan & Approval Surat 3-Tier",
            ha='center', fontsize=9, fontweight='bold', color=C["navy"])

    # Start
    start = plt.Circle((lane_x, 7.8), 0.17, color=C["navy"], zorder=5)
    ax.add_patch(start)
    arr(ax, lane_x, 7.63, lane_x, 7.2)

    draw_rounded_box(ax, lane_x, 7.0, 2.6, 0.38, "Warga: Isi & Submit Form Surat", C["teal"]+"33", C["teal"])
    arr(ax, lane_x, 6.81, lane_x, 6.4)
    draw_rounded_box(ax, lane_x, 6.2, 2.6, 0.38, "Validasi Input (Server-side)", C["sky"]+"33", C["sky"])
    arr(ax, lane_x, 6.01, lane_x, 5.55)
    draw_diamond(ax, lane_x, 5.3, 2.4, 0.5, "Valid?")
    # Invalid path
    ax.annotate("", xy=(1.4, 7.0), xytext=(2.3, 5.3),
                arrowprops=dict(arrowstyle="->", color=C["red"], lw=1.4))
    ax.text(1.1, 6.15, "Tidak", fontsize=7.5, color=C["red"], ha='center')

    arr(ax, lane_x, 5.05, lane_x, 4.6)
    ax.text(lane_x + 0.15, 4.83, "Ya", fontsize=7.5, color=C["green"])
    draw_rounded_box(ax, lane_x, 4.4, 2.6, 0.38, "Status: MENUNGGU_RT", C["blue"]+"22", C["blue"])
    arr(ax, lane_x, 4.21, lane_x, 3.75)
    draw_diamond(ax, lane_x, 3.5, 2.4, 0.5, "RT Approve?")
    # Reject RT
    ax.plot([2.3, 1.1, 1.1], [3.5, 3.5, 2.5], color=C["red"], lw=1.4)
    ax.annotate("", xy=(2.1, 2.5), xytext=(1.1, 2.5),
                arrowprops=dict(arrowstyle="->", color=C["red"], lw=1.4))
    ax.text(0.7, 3.5, "Tolak", fontsize=7, color=C["red"])

    arr(ax, lane_x, 3.25, lane_x, 2.75)
    ax.text(lane_x + 0.15, 3.0, "Ya", fontsize=7.5, color=C["green"])
    draw_rounded_box(ax, lane_x, 2.55, 2.6, 0.38, "Status: MENUNGGU_RW", C["purple"]+"22", C["purple"])
    arr(ax, lane_x, 2.36, lane_x, 1.9)
    draw_diamond(ax, lane_x, 1.65, 2.4, 0.5, "RW Approve?")

    arr(ax, lane_x, 1.4, lane_x, 0.9)
    ax.text(lane_x + 0.15, 1.15, "Ya", fontsize=7.5, color=C["green"])
    draw_rounded_box(ax, lane_x, 0.7, 2.6, 0.38, "Status: MENUNGGU_ADMIN_DESA", C["orange"]+"22", C["orange"])

    # ─── RIGHT LANE: Post-Approval & AI ───
    lane2_x = 9.5
    ax.add_patch(FancyBboxPatch((6.9, -0.3), 4.8, 8.6,
                                 boxstyle="round,pad=0.05",
                                 linewidth=1.5, edgecolor=C["gray"],
                                 facecolor="#f0fdf4", zorder=0, alpha=0.6))
    ax.text(9.3, 8.2, "Post-Approval & AI Chatbot",
            ha='center', fontsize=9, fontweight='bold', color=C["navy"])

    draw_diamond(ax, lane2_x, 7.0, 2.6, 0.5, "Admin Desa\nApprove?", C["orange"])
    draw_rounded_box(ax, lane2_x, 5.9, 2.6, 0.38, "Generate PDF (DomPDF)", C["green"]+"22", C["green"])
    draw_rounded_box(ax, lane2_x, 5.1, 2.6, 0.38, "Send Email (SuratSelesaiMail)", C["sky"]+"22", C["sky"])
    draw_rounded_box(ax, lane2_x, 4.3, 2.6, 0.38, "Notif WA via Fonnte", C["teal"]+"22", C["teal"])
    draw_rounded_box(ax, lane2_x, 3.5, 2.6, 0.38, "Status: SELESAI ✓", C["green"]+"44", C["green"])

    arr(ax, lane2_x, 7.25, lane2_x, 6.1)
    ax.text(lane2_x + 0.15, 6.7, "Approve", fontsize=7.5, color=C["green"])
    arr(ax, lane2_x, 5.71, lane2_x, 5.3)
    arr(ax, lane2_x, 4.91, lane2_x, 4.5)
    arr(ax, lane2_x, 4.11, lane2_x, 3.7)

    # Reject by Admin
    ax.plot([8.2, 7.5, 7.5], [7.0, 7.0, 5.9], color=C["red"], lw=1.4)
    ax.text(7.3, 6.5, "Tolak", fontsize=7, color=C["red"], rotation=90)

    # AI Chatbot section
    draw_rounded_box(ax, lane2_x, 2.5, 2.6, 0.38, "User: Kirim Pesan ke AI", C["purple"]+"22", C["purple"])
    draw_rounded_box(ax, lane2_x, 1.75, 2.6, 0.38, "Inject System Context Desa", C["purple"]+"22", C["purple"])
    draw_rounded_box(ax, lane2_x, 1.0, 2.6, 0.38, "GeminiService → Google AI API", C["sky"]+"22", C["sky"])
    draw_rounded_box(ax, lane2_x, 0.25, 2.6, 0.38, "Tampilkan Jawaban AI", C["purple"]+"22", C["purple"])

    arr(ax, lane2_x, 2.31, lane2_x, 1.94)
    arr(ax, lane2_x, 1.56, lane2_x, 1.19)
    arr(ax, lane2_x, 0.81, lane2_x, 0.44)

    # Connect left lane output to right lane
    ax.annotate("", xy=(8.2, 7.0), xytext=(6.6, 0.7),
                arrowprops=dict(arrowstyle="->", color=C["orange"],
                                lw=1.6, connectionstyle="angle,angleA=0,angleB=-90"))
    ax.text(7.0, 3.8, "Approve", fontsize=7.5, color=C["orange"], rotation=90)

    # End circles
    for ex, ey in [(lane2_x, -0.1), (lane_x, 0.4)]:
        end_out = plt.Circle((ex, ey - 0.22), 0.17, color=C["navy"], zorder=5)
        end_in  = plt.Circle((ex, ey - 0.22), 0.10, color=C["white"], zorder=6)
        ax.add_patch(end_out); ax.add_patch(end_in)

    fig.tight_layout(pad=0.2)
    return savefig(fig, "02_activity.png")


# ─────────────────────────────────────────────────────────────────────────────
# DIAGRAM 3: CLASS DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
def draw_class_box(ax, x, y, w, h, name, stereotype="", attributes=None, methods=None,
                   header_color=C["blue"], fontsize=7.5):
    """Draw a UML class box."""
    attributes = attributes or []
    methods     = methods or []

    n_attrs   = len(attributes)
    n_methods = len(methods)
    row_h     = 0.22  # height per row

    header_h  = 0.38 if not stereotype else 0.52
    attrs_h   = max(n_attrs * row_h, 0.22)
    methods_h = max(n_methods * row_h, 0.22)
    total_h   = header_h + attrs_h + methods_h

    # Header
    hdr = FancyBboxPatch((x - w/2, y), w, header_h,
                          boxstyle="round,pad=0.02",
                          facecolor=header_color, edgecolor=header_color,
                          linewidth=1.5, zorder=4)
    ax.add_patch(hdr)
    if stereotype:
        ax.text(x, y + header_h - 0.10, f"«{stereotype}»",
                ha='center', va='top', fontsize=fontsize - 1.5,
                color=C["white"], style='italic', zorder=5)
    ax.text(x, y + 0.05, name, ha='center', va='bottom',
            fontsize=fontsize + 0.5, fontweight='bold', color=C["white"], zorder=5)

    # Attributes section
    attr_rect = FancyBboxPatch((x - w/2, y - attrs_h), w, attrs_h,
                                boxstyle="square,pad=0",
                                facecolor=C["white"], edgecolor=header_color,
                                linewidth=1.2, zorder=3)
    ax.add_patch(attr_rect)
    for i, attr in enumerate(attributes):
        ax.text(x - w/2 + 0.08, y - i*row_h - 0.08, attr,
                ha='left', va='top', fontsize=fontsize - 1,
                color=C["black"], fontfamily='monospace', zorder=5)

    # Methods section
    method_rect = FancyBboxPatch((x - w/2, y - attrs_h - methods_h), w, methods_h,
                                  boxstyle="square,pad=0",
                                  facecolor="#f8faff", edgecolor=header_color,
                                  linewidth=1.2, zorder=3)
    ax.add_patch(method_rect)
    for i, m in enumerate(methods):
        ax.text(x - w/2 + 0.08, y - attrs_h - i*row_h - 0.08, m,
                ha='left', va='top', fontsize=fontsize - 1,
                color=C["navy"], fontfamily='monospace', zorder=5)

    return total_h

def diagram_class():
    fig, ax = plt.subplots(figsize=(A4_W, A4_H), facecolor=C["bg"])
    ax.set_xlim(0, 12); ax.set_ylim(-1.0, 9); ax.axis('off')
    ax.set_facecolor(C["bg"])

    title_bar(ax, "CLASS DIAGRAM — PrajaDigital",
              "Core Controllers · Services · Models · Mail")

    fs = 7.0
    # ─ Abstract Controller ─
    draw_class_box(ax, 6.0, 8.3, 2.5, 0.0, "Controller",
                   stereotype="abstract",
                   methods=["+ middleware(): void", "+ authorize(): bool"],
                   header_color=C["navy"], fontsize=fs)

    # ─ Child Controllers ─
    draw_class_box(ax, 2.0, 7.0, 2.6, 0.0, "PengajuanSuratController",
                   attributes=["- STATUSES: array"],
                   methods=["+ index(): View", "+ store(): RedirectResponse",
                             "+ approve(): RedirectResponse", "+ reject(): RedirectResponse",
                             "+ generatePdf(): Response"],
                   header_color=C["blue"], fontsize=fs)

    draw_class_box(ax, 6.0, 7.0, 2.6, 0.0, "KonfigurasiController",
                   attributes=["- ALLOWED_AI_MODELS: array"],
                   methods=["+ index(): View", "+ update(): RedirectResponse",
                             "+ resolveAiModel(): string", "+ resolveAiApiKeyFallback(): string"],
                   header_color=C["purple"], fontsize=fs)

    draw_class_box(ax, 10.0, 7.0, 2.6, 0.0, "AiAssistantController",
                   attributes=[],
                   methods=["+ index(): View", "+ chat(): JsonResponse"],
                   header_color=C["teal"], fontsize=fs)

    # Inheritance arrows (hollow triangle arrowhead)
    for cx in [2.0, 6.0, 10.0]:
        ax.annotate("", xy=(6.0, 7.78), xytext=(cx, 7.0),
                    arrowprops=dict(arrowstyle="-|>", color=C["navy"],
                                   lw=1.6, mutation_scale=12))

    # ─ Services ─
    draw_class_box(ax, 2.0, 4.6, 2.6, 0.0, "GeminiService",
                   stereotype="service",
                   attributes=["- DEFAULT_MODEL: string", "- REQUEST_TIMEOUT: int"],
                   methods=["+ ask(prompt): string", "- buildPayload(): array",
                             "- handleFallback(): string", "- retryRequest(): Response"],
                   header_color=C["sky"], fontsize=fs)

    draw_class_box(ax, 6.0, 4.6, 2.6, 0.0, "WhatsappNotificationService",
                   stereotype="service",
                   attributes=[],
                   methods=["+ sendNotification(user, msg): bool"],
                   header_color=C["green"], fontsize=fs)

    draw_class_box(ax, 10.0, 4.6, 2.6, 0.0, "SettingService",
                   stereotype="service",
                   attributes=["- CIPHER: string (AES-256)"],
                   methods=["+ get(key): mixed", "+ set(key, val): void",
                             "+ encryptValue(): string", "+ decryptValue(): string"],
                   header_color=C["gold"], fontsize=fs)

    # Dependency arrows (dashed)
    deps = [
        (2.0, 4.6, 2.0, 5.6, "«use»"),   # PSC → GeminiService
        (6.0, 4.6, 2.0, 5.6, "«use»"),   # PSC → WNS (via PSC.x=2 bottom)
        (10.0, 4.6, 10.0, 5.6, "«use»"), # AAC → GeminiService (conceptual)
        (6.0, 4.6, 6.0, 5.6, "«use»"),   # Konfig → SettingService
    ]
    for x1, y1, x2, y2, lbl in deps:
        ax.annotate("", xy=(x1, y1), xytext=(x2, y2),
                    arrowprops=dict(arrowstyle="->", color=C["gray"],
                                   lw=1.3, linestyle='dashed'))

    # ─ Models ─
    draw_class_box(ax, 1.4, 2.4, 2.4, 0.0, "User",
                   stereotype="model",
                   attributes=["+ id: int", "+ name: string", "+ email: string",
                               "+ roles: BelongsToMany"],
                   methods=["+ hasRole(): bool", "+ scopeByRole(): Builder"],
                   header_color=C["orange"], fontsize=fs)

    draw_class_box(ax, 4.5, 2.4, 2.8, 0.0, "PengajuanSurat",
                   stereotype="model",
                   attributes=["+ id: int", "+ warga_id: FK", "+ jenis_surat: string",
                               "+ status: enum", "+ approved_at: timestamp"],
                   methods=["+ warga(): BelongsTo", "+ scopeByStatus(): Builder"],
                   header_color=C["orange"], fontsize=fs)

    draw_class_box(ax, 8.0, 2.4, 2.4, 0.0, "Warga",
                   stereotype="model",
                   attributes=["+ id: int", "+ user_id: FK", "+ nik: string",
                               "+ rt_id: FK", "+ rw_id: FK"],
                   methods=["+ user(): BelongsTo", "+ rt(): BelongsTo"],
                   header_color=C["orange"], fontsize=fs)

    draw_class_box(ax, 11.0, 2.4, 2.1, 0.0, "SystemSetting",
                   stereotype="model",
                   attributes=["+ key: string", "+ value: encrypted"],
                   methods=["+ getValue(): mixed"],
                   header_color=C["orange"], fontsize=fs)

    # ─ Mail ─
    draw_class_box(ax, 5.5, 0.4, 2.6, 0.0, "SuratSelesaiMail",
                   stereotype="Mailable",
                   attributes=["+ pengajuan: PengajuanSurat"],
                   methods=["+ envelope(): Envelope", "+ content(): Content",
                             "+ attachments(): array"],
                   header_color=C["red"], fontsize=fs)

    # Association arrows Model ↔ Controller
    ax.annotate("", xy=(2.0, 3.5), xytext=(2.0, 4.6),
                arrowprops=dict(arrowstyle="->", color=C["gray"], lw=1.2))

    fig.tight_layout(pad=0.2)
    return savefig(fig, "03_class.png")


# ─────────────────────────────────────────────────────────────────────────────
# DIAGRAM 4: SEQUENCE DIAGRAM
# ─────────────────────────────────────────────────────────────────────────────
def diagram_sequence():
    fig, ax = plt.subplots(figsize=(A4_W, A4_H), facecolor=C["bg"])
    ax.set_xlim(0, 12); ax.set_ylim(-0.5, 9.5); ax.axis('off')
    ax.set_facecolor(C["bg"])

    title_bar(ax, "SEQUENCE DIAGRAM — PrajaDigital",
              "Alur Approval Surat: Admin Desa → PDF → Email → WhatsApp")

    # Lifelines
    lifelines = [
        (1.0,  "Admin Desa\n(Browser)",     C["orange"]),
        (2.6,  "Middleware\n(Auth+Role)",    C["gray"]),
        (4.2,  "PengajuanSurat\nController", C["blue"]),
        (5.8,  "PengajuanSurat\nModel",      C["purple"]),
        (7.4,  "MySQL DB",                   C["navy"]),
        (9.0,  "DomPDF\nService",            C["green"]),
        (10.6, "Mail +\nFonnte WA",          C["teal"]),
    ]

    top_y    = 8.8
    line_top = 8.3
    line_bot = -0.2

    for x, label, col in lifelines:
        # Actor box
        box = FancyBboxPatch((x - 0.55, line_top), 1.1, 0.5,
                              boxstyle="round,pad=0.04",
                              facecolor=col, edgecolor=col,
                              linewidth=1.5, zorder=4)
        ax.add_patch(box)
        ax.text(x, line_top + 0.25, label, ha='center', va='center',
                fontsize=6.8, color=C["white"], fontweight='bold',
                multialignment='center', zorder=5)

        # Dashed lifeline
        ax.plot([x, x], [line_top, line_bot],
                color=col, lw=1.0, linestyle='--', alpha=0.5, zorder=2)

    # Messages
    messages = [
        # (from_x, to_x, y, label, is_return)
        (1.0, 2.6, 7.9, "POST /pengajuan/{id}/approve", False),
        (2.6, 4.2, 7.4, "forward request (Auth OK)", False),
        (4.2, 5.8, 6.9, "find(id)", False),
        (5.8, 7.4, 6.4, "SELECT * WHERE id=:id", False),
        (7.4, 5.8, 5.9, "PengajuanSurat instance", True),
        (5.8, 4.2, 5.4, "return $pengajuan", True),
        (4.2, 5.8, 4.9, "update(status=SELESAI, approved_at)", False),
        (5.8, 7.4, 4.4, "UPDATE pengajuan_surat SET ...", False),
        (7.4, 5.8, 3.9, "1 row affected", True),
        (4.2, 9.0, 3.4, "generatePdf($pengajuan)", False),
        (9.0, 4.2, 2.9, "PDF binary (base64)", True),
        (4.2, 10.6, 2.4, "Mail::to()->send(SuratSelesaiMail)", False),
        (4.2, 10.6, 1.9, "WhatsappService::send(notif)", False),
        (10.6, 4.2, 1.4, "true (sent OK)", True),
        (4.2, 1.0, 0.9, "redirect()->back()->with('success',...)", True),
    ]

    for x1, x2, y, label, is_ret in messages:
        col   = C["gray"] if is_ret else C["navy"]
        ls    = 'dashed' if is_ret else 'solid'
        style = "->" if not is_ret else "->|"
        ax.annotate("", xy=(x2, y), xytext=(x1, y),
                    arrowprops=dict(arrowstyle="->", color=col,
                                   lw=1.4, linestyle=ls))
        # Activation box on target
        box_x = min(x1, x2) + abs(x2 - x1) * 0.45
        ax.add_patch(FancyBboxPatch((min(x1,x2)*0.03 + max(x1,x2)*0.97 - 0.08,
                                     y - 0.08), 0.16, 0.16,
                                    boxstyle="square,pad=0",
                                    facecolor=col, edgecolor=col,
                                    alpha=0.5, zorder=3))

        mx = (x1 + x2) / 2
        va = 'bottom' if not is_ret else 'top'
        dy = 0.07 if not is_ret else -0.07
        ax.text(mx, y + dy, label, ha='center', va=va,
                fontsize=6.5, color=col, style='italic' if is_ret else 'normal')

    # Number the messages
    for i, (x1, x2, y, _, _) in enumerate(messages, 1):
        ax.text(min(x1,x2) + 0.08, y + 0.12, str(i)+".",
                fontsize=6.5, color=C["gray"], fontweight='bold')

    fig.tight_layout(pad=0.2)
    return savefig(fig, "04_sequence.png")


# ─────────────────────────────────────────────────────────────────────────────
# DIAGRAM 5: ERD
# ─────────────────────────────────────────────────────────────────────────────
def draw_erd_table(ax, x, y, w, table_name, columns, pk_col=None,
                   fk_cols=None, header_color=C["blue"]):
    fk_cols  = fk_cols or []
    row_h    = 0.22
    header_h = 0.35
    n_rows   = len(columns)
    total_h  = header_h + n_rows * row_h + 0.1

    # Header
    hdr = FancyBboxPatch((x, y - header_h), w, header_h,
                          boxstyle="round,pad=0.02",
                          facecolor=header_color, edgecolor=header_color,
                          linewidth=1.5, zorder=4)
    ax.add_patch(hdr)
    ax.text(x + w/2, y - header_h/2, table_name,
            ha='center', va='center', fontsize=7.5,
            fontweight='bold', color=C["white"], zorder=5)

    # Column rows
    body = FancyBboxPatch((x, y - total_h), w, total_h - header_h,
                           boxstyle="square,pad=0",
                           facecolor=C["white"], edgecolor=header_color,
                           linewidth=1.2, zorder=3)
    ax.add_patch(body)

    for i, col in enumerate(columns):
        cy = y - header_h - (i + 0.5) * row_h
        is_pk = (col.split()[0] == pk_col)
        is_fk = any(col.startswith(fk) for fk in fk_cols)
        col_color = C["gold"] if is_pk else (C["purple"] if is_fk else C["black"])
        prefix    = "[PK] " if is_pk else ("[FK] " if is_fk else "     ")
        ax.text(x + 0.06, cy, prefix + col,
                ha='left', va='center', fontsize=6.2,
                color=col_color, fontfamily='monospace', zorder=5)

    return total_h

def diagram_erd():
    fig, ax = plt.subplots(figsize=(A4_W, A4_H), facecolor=C["bg"])
    ax.set_xlim(0, 12); ax.set_ylim(-0.8, 9); ax.axis('off')
    ax.set_facecolor(C["bg"])

    title_bar(ax, "ENTITY RELATIONSHIP DIAGRAM — PrajaDigital",
              "Database Schema: rpl  |  [PK] Primary Key   [FK] Foreign Key")

    # Tables definition: (x, y_top, width, name, columns, pk, fks)
    tables = [
        # Users & Auth
        (0.1, 8.5, 2.3, "users",
         ["id INT PK", "name VARCHAR(255)", "email VARCHAR(255)",
          "password VARCHAR(255)", "created_at TIMESTAMP"],
         "id", ["role"]),

        (2.6, 8.5, 2.1, "roles",
         ["id INT PK", "name VARCHAR(125)", "guard_name VARCHAR(125)"],
         "id", []),

        (5.0, 8.5, 2.3, "model_has_roles",
         ["role_id INT FK", "model_type VARCHAR", "model_id INT FK"],
         None, ["role_id", "model_id"]),

        # Warga data
        (0.1, 6.5, 2.3, "warga",
         ["id INT PK", "user_id INT FK", "nik VARCHAR(16)",
          "nama VARCHAR(255)", "rt_id INT FK", "rw_id INT FK",
          "desa_id INT FK"],
         "id", ["user_id", "rt_id", "rw_id", "desa_id"]),

        (2.6, 6.5, 2.0, "rt",
         ["id INT PK", "nama_rt VARCHAR(10)", "ketua_id INT FK", "rw_id INT FK"],
         "id", ["ketua_id", "rw_id"]),

        (4.9, 6.5, 2.0, "rw",
         ["id INT PK", "nama_rw VARCHAR(10)", "ketua_id INT FK", "desa_id INT FK"],
         "id", ["ketua_id", "desa_id"]),

        (7.1, 6.5, 2.0, "desa",
         ["id INT PK", "nama_desa VARCHAR(255)", "kecamatan VARCHAR(100)",
          "kabupaten VARCHAR(100)"],
         "id", []),

        # Surat
        (0.1, 4.0, 2.4, "pengajuan_surat",
         ["id INT PK", "warga_id INT FK", "jenis_surat VARCHAR(100)",
          "status ENUM", "data_tambahan JSON", "approved_rt_at TIMESTAMP",
          "approved_rw_at TIMESTAMP", "approved_admin_at TIMESTAMP",
          "pdf_path VARCHAR(255)", "created_at TIMESTAMP"],
         "id", ["warga_id"]),

        (2.7, 4.0, 2.2, "surat_templates",
         ["id INT PK", "jenis_surat VARCHAR(100)", "template_path VARCHAR",
          "is_active BOOLEAN"],
         "id", []),

        # Settings & Logs
        (5.1, 4.0, 2.2, "system_settings",
         ["id INT PK", "key VARCHAR(100)", "value TEXT encrypted",
          "updated_at TIMESTAMP"],
         "id", []),

        (7.5, 4.0, 2.3, "audit_logs",
         ["id INT PK", "user_id INT FK", "action VARCHAR(255)",
          "model_type VARCHAR", "model_id INT", "created_at TIMESTAMP"],
         "id", ["user_id"]),

        # Permissions (Spatie)
        (5.1, 1.8, 2.2, "permissions",
         ["id INT PK", "name VARCHAR(125)", "guard_name VARCHAR(125)"],
         "id", []),

        (7.6, 1.8, 2.3, "role_has_permissions",
         ["permission_id INT FK", "role_id INT FK"],
         None, ["permission_id", "role_id"]),
    ]

    table_positions = {}
    for (x, y, w, name, cols, pk, fks) in tables:
        h = draw_erd_table(ax, x, y, w, name, cols, pk, fks,
                           header_color=C["blue"] if pk else C["gray"])
        # Center point of table
        table_positions[name] = (x + w/2, y - h/2)

    # Relationship lines (simplified crow's foot notation)
    relations = [
        ("users",      "warga",             "1", "1"),
        ("warga",      "pengajuan_surat",   "1", "N"),
        ("warga",      "rt",               "N", "1"),
        ("warga",      "rw",               "N", "1"),
        ("rt",         "rw",               "N", "1"),
        ("rw",         "desa",             "N", "1"),
        ("users",      "model_has_roles",   "1", "N"),
        ("roles",      "model_has_roles",   "1", "N"),
        ("roles",      "role_has_permissions","1","N"),
        ("permissions","role_has_permissions","1","N"),
        ("users",      "audit_logs",        "1", "N"),
    ]

    for t1, t2, card1, card2 in relations:
        if t1 in table_positions and t2 in table_positions:
            x1, y1 = table_positions[t1]
            x2, y2 = table_positions[t2]
            ax.plot([x1, x2], [y1, y2], color=C["gray"],
                    lw=1.0, linestyle=':', alpha=0.6, zorder=1)
            ax.text((x1+x2)/2, (y1+y2)/2, f"{card1}:{card2}",
                    fontsize=5.5, color=C["gray"], ha='center',
                    bbox=dict(facecolor=C["bg"], edgecolor='none', alpha=0.7))

    fig.tight_layout(pad=0.2)
    return savefig(fig, "05_erd.png")


# ─────────────────────────────────────────────────────────────────────────────
# COMPILE INTO PDF (using Pillow)
# ─────────────────────────────────────────────────────────────────────────────
def compile_pdf(png_paths):
    try:
        from PIL import Image
        pdf_path = os.path.join(os.path.dirname(__file__), "docs",
                                "02_UML_DIAGRAMS_FULLPAGE.pdf")
        images = [Image.open(p).convert("RGB") for p in png_paths]
        images[0].save(pdf_path, save_all=True, append_images=images[1:])
        kb = os.path.getsize(pdf_path) // 1024
        print(f"\n  ✓ Compiled PDF: {pdf_path}  ({kb} KB,  {len(images)} pages)")
    except Exception as e:
        print(f"\n  ⚠ PDF compilation skipped: {e}")


# ─────────────────────────────────────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    print("\n━━━ PrajaDigital UML Diagram Generator ━━━\n")
    paths = []
    steps = [
        ("01_usecase.png  — Use Case Diagram",   diagram_usecase),
        ("02_activity.png — Activity Diagram",   diagram_activity),
        ("03_class.png    — Class Diagram",       diagram_class),
        ("04_sequence.png — Sequence Diagram",    diagram_sequence),
        ("05_erd.png      — ERD Database rpl",    diagram_erd),
    ]
    for label, fn in steps:
        print(f"  Generating {label} ...", end=" ", flush=True)
        try:
            paths.append(fn())
        except Exception as ex:
            print(f"\n  ✗ ERROR: {ex}")
            import traceback; traceback.print_exc()

    if paths:
        print("\n  Compiling PDF...")
        compile_pdf(paths)

    print("\n━━━ Done! ━━━")
    print(f"  Output folder: {OUT_DIR}")
    for p in paths:
        kb = os.path.getsize(p) // 1024
        print(f"    {os.path.basename(p):30s} {kb:>5} KB")

