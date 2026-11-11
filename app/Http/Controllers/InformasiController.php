<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use App\Models\Rt;
use App\Models\Rw;
use App\Services\WhatsappNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola Informasi & Pengumuman Publik Bertarget.
 *
 * Mendukung target:
 * - Seluruh Warga Desa
 * - RW Tertentu
 * - RT Tertentu
 * - Role Tertentu
 */
class InformasiController extends Controller
{
    /**
     * Tampilkan daftar informasi dengan filter wilayah dan pencarian.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Informasi::with(['penulis', 'rw', 'rt', 'latestBroadcast'])->latest();

        // Admin melihat semua informasi
        if ($user->hasRole('super_admin')) {
            // No restriction
        } elseif ($user->hasRole('rw')) {
            // RW melihat pengumuman umum/desa, atau khusus RW-nya, atau role rw
            $query->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                    ->orWhere('target_type', 'desa')
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('target_type', 'rw')->where('rw_id', $user->rw_id);
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('target_type', 'rt')->where('rw_id', $user->rw_id);
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('target_type', 'role')->where('target_role', 'rw');
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('rw_id')->orWhere('rw_id', $user->rw_id);
                    });
            });
        } elseif ($user->hasRole('rt')) {
            // RT melihat pengumuman umum/desa, RW induk, RT-nya, atau role rt
            $query->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                    ->orWhere('target_type', 'desa')
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('target_type', 'rw')->where('rw_id', $user->rw_id);
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('target_type', 'rt')->where('rt_id', $user->rt_id);
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('target_type', 'role')->where('target_role', 'rt');
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('rw_id')
                            ->orWhere(function ($w) use ($user) {
                                $w->where('rw_id', $user->rw_id)->whereNull('rt_id');
                            })
                            ->orWhere('rt_id', $user->rt_id);
                    });
            });
        } elseif ($user->hasRole('warga')) {
            $warga = $user->warga;
            $userRwId = $warga?->rw_id ?? $user->rw_id;
            $userRtId = $warga?->rt_id ?? $user->rt_id;

            $query->where('status_publikasi', true)
                ->where(function ($q) use ($userRwId, $userRtId) {
                    $q->where('target_type', 'desa')
                        ->orWhere(function ($sub) use ($userRwId) {
                            $sub->where('target_type', 'rw')->where('rw_id', $userRwId);
                        })
                        ->orWhere(function ($sub) use ($userRwId, $userRtId) {
                            $sub->where('target_type', 'rt')->where('rw_id', $userRwId)->where('rt_id', $userRtId);
                        })
                        ->orWhere(function ($sub) {
                            $sub->where('target_type', 'role')->where('target_role', 'warga');
                        })
                        ->orWhere(function ($sub) use ($userRwId, $userRtId) {
                            $sub->whereNull('rw_id')
                                ->orWhere(function ($w) use ($userRwId) {
                                    $w->where('rw_id', $userRwId)->whereNull('rt_id');
                                })
                                ->orWhere('rt_id', $userRtId);
                        });
                });
        }

        if ($request->search) {
            $query->where('judul', 'like', '%'.$request->search.'%');
        }

        $informasis = $query->paginate(15);

        return view('informasi.index', compact('informasis'));
    }

    /**
     * Tampilkan form untuk membuat informasi baru.
     */
    public function create(): View
    {
        $this->authorize('create', Informasi::class);

        $user = auth()->user();
        $rws = collect();
        $rts = collect();

        if ($user->hasRole('super_admin')) {
            $rws = Rw::with('rts')->where('status', true)->get();
            $rts = Rt::where('status', true)->get();
        } elseif ($user->hasRole('rw')) {
            $rws = Rw::with('rts')->where('id', $user->rw_id)->get();
            $rts = Rt::where('rw_id', $user->rw_id)->where('status', true)->get();
        } elseif ($user->hasRole('rt')) {
            $rws = Rw::where('id', $user->rw_id)->get();
            $rts = Rt::where('id', $user->rt_id)->get();
        }

        return view('informasi.create', compact('rws', 'rts'));
    }

    /**
     * Simpan informasi baru ke database dengan pembatasan target di backend.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Informasi::class);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:50',
            'konten' => 'nullable|string',
            'isi' => 'nullable|string',
            'target_type' => 'required|in:desa,rw,rt,role',
            'target_role' => 'nullable|in:super_admin,rw,rt,warga',
            'rw_id' => 'nullable|exists:rw,id',
            'rt_id' => 'nullable|exists:rt,id',
            'status_publikasi' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ]);

        $isiKonten = $validated['isi'] ?? $validated['konten'] ?? '';
        if (empty($isiKonten)) {
            return back()->withInput()->withErrors(['isi' => 'Konten / isi informasi wajib diisi.']);
        }

        $user = auth()->user();
        $targetType = $validated['target_type'];
        $rwId = $validated['rw_id'] ?? null;
        $rtId = $validated['rt_id'] ?? null;
        $targetRole = $validated['target_role'] ?? null;

        // Validasi batasan kewenangan wilayah di backend
        if ($user->hasRole('rt')) {
            // RT hanya berhak membuat informasi untuk RT miliknya sendiri
            $targetType = 'rt';
            $rwId = $user->rw_id;
            $rtId = $user->rt_id;
            $targetRole = null;
        } elseif ($user->hasRole('rw')) {
            // RW hanya berhak untuk wilayah RW-nya atau RT di bawah RW-nya
            if ($targetType === 'desa' || $targetType === 'role') {
                return back()->withInput()->withErrors(['target_type' => 'Ketua RW hanya berwenang membuat pengumuman untuk lingkup RW atau RT binaannya.']);
            }
            $rwId = $user->rw_id;
            if ($targetType === 'rt') {
                // Pastikan RT yang dipilih berada di bawah RW user
                if (! $rtId || ! Rt::where('id', $rtId)->where('rw_id', $user->rw_id)->exists()) {
                    return back()->withInput()->withErrors(['rt_id' => 'RT yang dipilih tidak valid di bawah RW Anda.']);
                }
            } else {
                $rtId = null;
            }
        } else {
            // Super Admin
            if ($targetType === 'desa') {
                $rwId = null;
                $rtId = null;
                $targetRole = null;
            } elseif ($targetType === 'rw') {
                if (! $rwId) {
                    return back()->withInput()->withErrors(['rw_id' => 'Silakan pilih RW target.']);
                }
                $rtId = null;
            } elseif ($targetType === 'rt') {
                if (! $rwId || ! $rtId) {
                    return back()->withInput()->withErrors(['rt_id' => 'Pilih RW dan RT target.']);
                }
            } elseif ($targetType === 'role') {
                if (! $targetRole) {
                    return back()->withInput()->withErrors(['target_role' => 'Pilih role target pengumuman.']);
                }
                $rwId = null;
                $rtId = null;
            }
        }

        $scopeWilayah = match ($targetType) {
            'rt' => 'RT',
            'rw' => 'RW',
            default => 'Desa',
        };

        $isPublished = $request->boolean('status_publikasi') || $request->boolean('is_published');

        $informasi = Informasi::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $isiKonten,
            'author_id' => $user->id,
            'status_publikasi' => $isPublished,
            'scope_wilayah' => $scopeWilayah,
            'target_type' => $targetType,
            'target_role' => $targetRole,
            'rw_id' => $rwId,
            'rt_id' => $rtId,
            'tanggal' => now(),
        ]);

        return redirect()->route('informasi.show', $informasi->id)->with('success', 'Informasi berhasil disimpan.');
    }

    /**
     * Tampilkan detail informasi dan riwayat pengiriman WhatsApp.
     */
    public function show(Informasi $informasi, WhatsappNotificationService $waService): View
    {
        $this->authorize('view', $informasi);

        $informasi->load(['penulis', 'rw', 'rt', 'broadcasts.sender', 'broadcasts.recipients']);

        // Hitung estimasi penerima untuk modal konfirmasi WhatsApp
        $estimatedRecipients = $waService->resolveRecipients($informasi);
        $isSimulationMode = $waService->isSimulationMode();

        return view('informasi.show', compact('informasi', 'estimatedRecipients', 'isSimulationMode'));
    }

    /**
     * Tampilkan form untuk mengubah informasi.
     */
    public function edit(Informasi $informasi): View
    {
        $this->authorize('update', $informasi);

        $user = auth()->user();
        $rws = collect();
        $rts = collect();

        if ($user->hasRole('super_admin')) {
            $rws = Rw::with('rts')->where('status', true)->get();
            $rts = Rt::where('status', true)->get();
        } elseif ($user->hasRole('rw')) {
            $rws = Rw::with('rts')->where('id', $user->rw_id)->get();
            $rts = Rt::where('rw_id', $user->rw_id)->where('status', true)->get();
        } elseif ($user->hasRole('rt')) {
            $rws = Rw::where('id', $user->rw_id)->get();
            $rts = Rt::where('id', $user->rt_id)->get();
        }

        return view('informasi.edit', compact('informasi', 'rws', 'rts'));
    }

    /**
     * Perbarui data informasi di database.
     */
    public function update(Request $request, Informasi $informasi): RedirectResponse
    {
        $this->authorize('update', $informasi);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:50',
            'konten' => 'nullable|string',
            'isi' => 'nullable|string',
            'target_type' => 'required|in:desa,rw,rt,role',
            'target_role' => 'nullable|in:super_admin,rw,rt,warga',
            'rw_id' => 'nullable|exists:rw,id',
            'rt_id' => 'nullable|exists:rt,id',
            'status_publikasi' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ]);

        $isiKonten = $validated['isi'] ?? $validated['konten'] ?? $informasi->isi;

        $user = auth()->user();
        $targetType = $validated['target_type'];
        $rwId = $validated['rw_id'] ?? null;
        $rtId = $validated['rt_id'] ?? null;
        $targetRole = $validated['target_role'] ?? null;

        if ($user->hasRole('rt')) {
            $targetType = 'rt';
            $rwId = $user->rw_id;
            $rtId = $user->rt_id;
            $targetRole = null;
        } elseif ($user->hasRole('rw')) {
            if ($targetType === 'desa' || $targetType === 'role') {
                return back()->withInput()->withErrors(['target_type' => 'Ketua RW hanya berwenang untuk lingkup RW atau RT binaannya.']);
            }
            $rwId = $user->rw_id;
            if ($targetType === 'rt') {
                if (! $rtId || ! Rt::where('id', $rtId)->where('rw_id', $user->rw_id)->exists()) {
                    return back()->withInput()->withErrors(['rt_id' => 'RT yang dipilih tidak valid di bawah RW Anda.']);
                }
            } else {
                $rtId = null;
            }
        }

        $scopeWilayah = match ($targetType) {
            'rt' => 'RT',
            'rw' => 'RW',
            default => 'Desa',
        };

        $isPublished = $request->boolean('status_publikasi') || $request->boolean('is_published');

        $informasi->update([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $isiKonten,
            'status_publikasi' => $isPublished,
            'scope_wilayah' => $scopeWilayah,
            'target_type' => $targetType,
            'target_role' => $targetRole,
            'rw_id' => $rwId,
            'rt_id' => $rtId,
        ]);

        return redirect()->route('informasi.show', $informasi->id)->with('success', 'Informasi berhasil diperbarui.');
    }

    /**
     * Endpoint API/Form untuk memicu pengiriman broadcast WhatsApp dengan konfirmasi eksplisit.
     */
    public function broadcastWhatsapp(Request $request, Informasi $informasi, WhatsappNotificationService $waService): RedirectResponse
    {
        $this->authorize('update', $informasi);

        // Validasi konfirmasi eksplisit dari pengguna
        $request->validate([
            'confirm_broadcast' => 'required|accepted',
        ], [
            'confirm_broadcast.accepted' => 'Anda harus mencentang konfirmasi pengiriman WhatsApp terlebih dahulu.',
        ]);

        if (! $informasi->status_publikasi) {
            return redirect()->route('informasi.show', $informasi->id)
                ->with('error', 'Informasi harus dipublikasikan terlebih dahulu sebelum dapat disiarkan via WhatsApp.');
        }

        $broadcast = $waService->executeBroadcast($informasi, auth()->user());

        $modeLabel = $waService->isSimulationMode() ? '[Mode Simulasi]' : '[Live Fonnte]';

        if ($broadcast->total_recipients === 0) {
            return redirect()->route('informasi.show', $informasi->id)
                ->with('warning', 'Tidak ada penerima dengan nomor WhatsApp valid untuk target ini, atau seluruh penerima telah menerima pesan sebelumnya.');
        }

        $msg = "{$modeLabel} Berhasil memproses pengiriman ke {$broadcast->success_count} dari {$broadcast->total_recipients} penerima.";
        if ($broadcast->failed_count > 0) {
            $msg .= " ({$broadcast->failed_count} gagal/kendala: {$broadcast->error_summary})";
        }

        return redirect()->route('informasi.show', $informasi->id)->with('success', $msg);
    }

    /**
     * Hapus informasi dari database.
     */
    public function destroy(Informasi $informasi): RedirectResponse
    {
        $this->authorize('delete', $informasi);

        $informasi->delete();

        return redirect()->route('informasi.index')->with('success', 'Informasi berhasil dihapus.');
    }
}
