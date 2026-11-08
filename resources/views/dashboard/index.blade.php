@extends('layouts.app')

@section('title', 'Dashboard - Accounting System')

@section('content')

{{-- HEADER --}}
<div class="page-header" style="margin-bottom: 24px;">
    <div class="company-tag">
        Sistem Informasi Akuntansi
    </div>
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1>Dashboard Ringkasan</h1>
            <p>Selamat datang! Berikut ringkasan aktivitas keuangan per <strong>{{ $currentPeriod }}</strong></p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('journals.index') }}" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 0.9rem;">
                <span>+</span> Input Jurnal
            </a>
            <a href="{{ route('reports.index') }}" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 0.9rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b; font-weight: 600;">
                <span>📈</span> Laba Rugi
            </a>
        </div>
    </div>
</div>

{{-- METRIC CARDS --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 24px;">

    <!-- Total Pendapatan Bulan Ini -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #10b981; position: relative; margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Pendapatan Bulan Ini</span>
            <span style="font-size: 1.25rem;">💰</span>
        </div>
        <div style="font-size: 1.45rem; font-weight: 800; color: #10b981; font-family: var(--mono, monospace);">
            Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">
            Periode {{ $currentPeriod }}
        </div>
    </div>

    <!-- Total Beban Bulan Ini -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #ef4444; position: relative; margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Beban Bulan Ini</span>
            <span style="font-size: 1.25rem;">💸</span>
        </div>
        <div style="font-size: 1.45rem; font-weight: 800; color: #ef4444; font-family: var(--mono, monospace);">
            Rp {{ number_format($monthlyExpense, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">
            Total pengeluaran tercatat
        </div>
    </div>

    <!-- Laba/Rugi Bersih Bulan Ini -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid {{ $monthlyNetIncome >= 0 ? '#0ea5e9' : '#f97316' }}; position: relative; margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Estimasi Laba / Rugi</span>
            <span style="font-size: 1.25rem;">{{ $monthlyNetIncome >= 0 ? '📊' : '📉' }}</span>
        </div>
        <div style="font-size: 1.45rem; font-weight: 800; color: {{ $monthlyNetIncome >= 0 ? '#0ea5e9' : '#f97316' }}; font-family: var(--mono, monospace);">
            Rp {{ number_format($monthlyNetIncome, 0, ',', '.') }}
        </div>
        <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; font-weight: 600;">
            {{ $monthlyNetIncome >= 0 ? '✓ Surplus / Laba' : '✗ Defisit / Rugi' }}
        </div>
    </div>

    <!-- Ringkasan Akun & Aktivitas -->
    <div class="card" style="padding: 18px 20px; border-left: 4px solid #8b5cf6; position: relative; margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Aktivitas Jurnal</span>
            <span style="font-size: 1.25rem;">📑</span>
        </div>
        <div style="font-size: 1.45rem; font-weight: 800; color: #1e293b;">
            {{ $totalJournalsMonth }} <span style="font-size: 0.9rem; font-weight: 500; color: #64748b;">Transaksi</span>
        </div>
        <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">
            {{ $totalAccounts }} Akun COA terdaftar
        </div>
    </div>

</div>

{{-- GRID KONTEN BAWAH --}}
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">

    <!-- KOLOM KIRI: RINGKASAN KELOMPOK AKUN & PANDUAN NORMAL SALDO -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- 1. Ringkasan Kelompok Akun Keuangan -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">🏛 Ringkasan 5 Kelompok Akun Utama</h3>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 3px 0 0;">Status saldo mutasi akun per kelompok bulan ini</p>
                </div>
                <a href="{{ route('chart-of-accounts.index') }}" style="font-size: 0.85rem; color: var(--accent, #62c4da); text-decoration: none; font-weight: 600;">
                    Kelola COA &rarr;
                </a>
            </div>

            <div class="tbl-wrap">
                <table class="trial-table" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Kode</th>
                            <th>Kelompok Akun</th>
                            <th style="text-align: center;">Jumlah Akun</th>
                            <th style="text-align: center;">Posisi Normal</th>
                            <th class="num">Saldo Bulan Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupStats as $grp)
                            <tr>
                                <td class="font-mono" style="font-weight: 700; color: #0284c7;">
                                    {{ $grp->group_code }}
                                </td>
                                <td style="font-weight: 600; color: #1e293b;">
                                    {{ $grp->group_name }}
                                </td>
                                <td style="text-align: center; color: #64748b;">
                                    {{ $grp->total_coa }} Akun
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700; background: {{ $grp->normal_pos === 'Debit' ? '#dbeafe' : '#fef3c7' }}; color: {{ $grp->normal_pos === 'Debit' ? '#1e40af' : '#92400e' }};">
                                        {{ $grp->normal_pos }}
                                    </span>
                                </td>
                                <td class="num font-mono" style="font-weight: 700; color: {{ $grp->net_balance >= 0 ? '#0f172a' : '#ef4444' }};">
                                    Rp {{ number_format($grp->net_balance, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="trial-empty" style="padding: 20px;">
                                    Belum ada data kelompok akun.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Jurnal Transaksi Terakhir -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">⚡ Transaksi Jurnal Terakhir</h3>
                    <p style="font-size: 0.8rem; color: #64748b; margin: 3px 0 0;">5 riwayat transaksi jurnal umum yang baru dicatat</p>
                </div>
                <a href="{{ route('journals.index') }}" style="font-size: 0.85rem; color: var(--accent, #62c4da); text-decoration: none; font-weight: 600;">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="tbl-wrap">
                <table class="trial-table" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th>No. Jurnal</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th class="num">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentJournals as $journal)
                            <tr>
                                <td class="font-mono" style="font-weight: 600; color: #0284c7;">
                                    {{ $journal->journal_number }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($journal->transaction_date)->format('d M Y') }}
                                </td>
                                <td>
                                    {{ $journal->description ?? '-' }}
                                </td>
                                <td class="num font-mono" style="font-weight: 600;">
                                    Rp {{ number_format($journal->total_debit, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="trial-empty" style="padding: 20px;">
                                    Belum ada transaksi jurnal yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- KOLOM KANAN: PANDUAN PINTAS AKUNTANSI & TOP BEBAN -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- 1. Akses Menu Utama -->
        <div class="card" style="background: linear-gradient(135deg, #f6ffea 0%, #fff 100%); border: 1px solid #d9edd0; margin-bottom: 0;">
            <div class="card-title" style="margin-bottom: 8px;">🚀 Akses Cepat</div>
            <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 14px;">Menu operasional yang paling sering digunakan:</p>

            <div style="display: flex; flex-direction: column; gap: 9px;">
                <a href="{{ route('journals.index') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #1e293b; font-weight: 600; font-size: 0.88rem; transition: all 0.2s;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span>📒</span> Jurnal Umum
                    </span>
                    <span style="color: #94a3b8;">&rsaquo;</span>
                </a>

                <a href="{{ route('reports.index') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #1e293b; font-weight: 600; font-size: 0.88rem; transition: all 0.2s;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span>📈</span> Laporan Laba Rugi
                    </span>
                    <span style="color: #94a3b8;">&rsaquo;</span>
                </a>

                <a href="{{ route('chart-of-accounts.index') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #1e293b; font-weight: 600; font-size: 0.88rem; transition: all 0.2s;">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        <span>▤</span> Daftar Akun (COA)
                    </span>
                    <span style="color: #94a3b8;">&rsaquo;</span>
                </a>
            </div>
        </div>

        <!-- 2. Kartu Panduan Saldo Normal (Sangat berguna saat input jurnal) -->
        <div class="card" style="margin-bottom: 0;">
            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 8px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>📖</span> Panduan Debit / Kredit
            </div>
            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 12px; line-height: 1.4;">
                Pedoman praktis posisi saldo bertambah saat menjurnal:
            </p>

            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.82rem;">
                <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #eff6ff; border-radius: 6px; border-left: 3px solid #3b82f6;">
                    <span><strong>(1) Aset</strong> (Kas, Bank)</span>
                    <span style="font-weight: 700; color: #1d4ed8;">+ Debit / - Kredit</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #fefce8; border-radius: 6px; border-left: 3px solid #eab308;">
                    <span><strong>(2) Kewajiban</strong> (Hutang)</span>
                    <span style="font-weight: 700; color: #a16207;">+ Kredit / - Debit</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #fdf2f8; border-radius: 6px; border-left: 3px solid #ec4899;">
                    <span><strong>(3) Modal / Ekuitas</strong></span>
                    <span style="font-weight: 700; color: #be185d;">+ Kredit / - Debit</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #ecfdf5; border-radius: 6px; border-left: 3px solid #10b981;">
                    <span><strong>(4) Pendapatan</strong></span>
                    <span style="font-weight: 700; color: #047857;">+ Kredit / - Debit</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 10px; background: #fef2f2; border-radius: 6px; border-left: 3px solid #ef4444;">
                    <span><strong>(5/6) Beban / Biaya</strong></span>
                    <span style="font-weight: 700; color: #b91c1c;">+ Debit / - Kredit</span>
                </div>
            </div>
        </div>

        <!-- 3. Top 5 Beban Pengeluaran Terbesar Bulan Ini -->
        @if($topExpenses->isNotEmpty())
            <div class="card" style="margin-bottom: 0;">
                <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 8px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>🔥</span> Pengeluaran Terbesar
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($topExpenses as $exp)
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; border-bottom: 1px dashed #e2e8f0; padding-bottom: 6px;">
                            <span style="color: #334155;">{{ $exp->name }}</span>
                            <span class="font-mono" style="font-weight: 700; color: #ef4444;">
                                Rp {{ number_format($exp->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>

@endsection
