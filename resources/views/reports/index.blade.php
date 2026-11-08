@extends('layouts.app')

@section('title', 'Laporan Keuangan - Laba Rugi')

@section('content')

<div class="page-header">
    <div class="company-tag">
        Laporan Keuangan / Financial Statements
    </div>

    <h1>📈 Laporan Laba Rugi (Income Statement)</h1>
    <p>Periode: {{ $period }}</p>
</div>

{{-- FILTER PERIODE --}}
<div class="trial-toolbar no-print">

    <form action="{{ route('reports.index') }}" method="GET">

        <div class="trial-filter">
            <label for="month">Bulan</label>

            <select name="month" id="month">
                @foreach([
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ] as $number => $name)

                    <option value="{{ $number }}"
                        {{ $month == $number ? 'selected' : '' }}>
                        {{ $name }}
                    </option>

                @endforeach
            </select>
        </div>

        <div class="trial-filter">
            <label for="year">Tahun</label>

            <select name="year" id="year">
                @foreach($years as $y)
                    <option value="{{ $y }}"
                        {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary">
            Tampilkan
        </button>

    </form>

    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <!-- Export 1 Bulan -->
        <a href="{{ route('reports.export-excel', ['year' => $year, 'month' => $month, 'mode' => 'single']) }}" 
           class="btn-secondary" 
           title="Export Laporan Laba Rugi untuk bulan {{ $period }}"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 0.88rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b; font-weight: 600;">
            <span>📥</span> Export Excel (Bulan Ini)
        </a>

        <!-- Export Semua Bulan (Multi-Sheet) -->
        <a href="{{ route('reports.export-excel', ['year' => $year, 'mode' => 'all']) }}" 
           class="btn-primary" 
           title="Export semua bulan tahun {{ $year }} dalam masing-masing sheet"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 0.88rem; background: #10b981; border: 1px solid #059669; border-radius: 6px; color: #ffffff; font-weight: 600;">
            <span>📑</span> Export Excel (Semua Bulan)
        </a>
    </div>

</div>

{{-- RINGKASAN STATISTIK --}}
<div class="trial-stats">

    <div class="trial-stat-card">
        <div class="stat-label">TOTAL PENDAPATAN</div>
        <div class="stat-value positive">
            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        </div>
    </div>

    <div class="trial-stat-card">
        <div class="stat-label">TOTAL BEBAN</div>
        <div class="stat-value negative">
            Rp {{ number_format($totalExpense, 0, ',', '.') }}
        </div>
    </div>

    <div class="trial-stat-card">
        <div class="stat-label">LABA / (RUGI) BERSIH</div>
        <div class="stat-value {{ $netIncome >= 0 ? 'positive' : 'negative' }}">
            Rp {{ number_format($netIncome, 0, ',', '.') }}
            <span style="font-size: 0.8rem; display: block; font-weight: normal; margin-top: 4px;">
                {{ $netIncome >= 0 ? '✓ LABA (Surplus)' : '✗ RUGI (Defisit)' }}
            </span>
        </div>
    </div>

</div>

{{-- TABEL LAPORAN LABA RUGI --}}
<div class="card trial-card">

    <div class="trial-card-heading">
        <h3>Laporan Laba Rugi</h3>
        <p>Periode: {{ $period }}</p>
    </div>

    <div class="tbl-wrap">

        <table class="trial-table">

            <thead>
                <tr>
                    <th style="width: 15%;">Kode Akun</th>
                    <th>Nama Akun</th>
                    <th style="width: 25%;">Kategori</th>
                    <th class="num" style="width: 25%;">Jumlah (Rp)</th>
                </tr>
            </thead>

            <tbody>

                {{-- SECTION PENDAPATAN --}}
                <tr style="background-color: #f8fafc; font-weight: 700;">
                    <td colspan="4" style="color: #0f172a; padding-top: 14px; padding-bottom: 14px;">
                        📌 PENDAPATAN
                    </td>
                </tr>

                @forelse($revenues as $rev)
                    <tr>
                        <td class="account-code">{{ $rev['code'] }}</td>
                        <td class="acc-name">{{ $rev['name'] }}</td>
                        <td style="color: #64748b;">{{ $rev['category'] }}</td>
                        <td class="num font-mono">
                            Rp {{ number_format($rev['amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="trial-empty" style="padding: 12px; font-size: 0.9rem;">
                            Tidak ada pendapatan pada periode ini.
                        </td>
                    </tr>
                @endforelse

                <tr style="background-color: #f1f5f9; font-weight: 600;">
                    <td colspan="3" style="text-align: right; padding-right: 15px;">TOTAL PENDAPATAN</td>
                    <td class="num font-mono" style="color: #10b981; font-weight: 700;">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </td>
                </tr>

                {{-- SECTION BEBAN --}}
                <tr style="background-color: #f8fafc; font-weight: 700;">
                    <td colspan="4" style="color: #0f172a; padding-top: 18px; padding-bottom: 14px;">
                        📌 BEBAN PENGELUARAN
                    </td>
                </tr>

                @forelse($expenses as $exp)
                    <tr>
                        <td class="account-code">{{ $exp['code'] }}</td>
                        <td class="acc-name">{{ $exp['name'] }}</td>
                        <td style="color: #64748b;">{{ $exp['category'] }}</td>
                        <td class="num font-mono">
                            Rp {{ number_format($exp['amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="trial-empty" style="padding: 12px; font-size: 0.9rem;">
                            Tidak ada beban pengeluaran pada periode ini.
                        </td>
                    </tr>
                @endforelse

                <tr style="background-color: #f1f5f9; font-weight: 600;">
                    <td colspan="3" style="text-align: right; padding-right: 15px;">TOTAL BEBAN</td>
                    <td class="num font-mono" style="color: #ef4444; font-weight: 700;">
                        Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </td>
                </tr>

                {{-- TOTAL BERSIH LABA / RUGI --}}
                <tr class="trial-total" style="border-top: 2px solid #0f172a; font-size: 1.05rem;">
                    <td colspan="3">
                        <strong>LABA / (RUGI) BERSIH</strong>
                    </td>
                    <td class="num font-mono" style="color: {{ $netIncome >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 800;">
                        Rp {{ number_format($netIncome, 0, ',', '.') }}
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>

@endsection

