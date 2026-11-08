
@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')

<div class="page-header">
    <div class="company-tag">
        Neraca Saldo / Trial Balance
    </div>

    <h1>⚖️ Neraca Saldo</h1>
    <p>Per {{ $period }}</p>
</div>

{{-- FILTER PERIODE --}}
<div class="trial-toolbar no-print">

    <form action="{{ route('neraca-saldo.index') }}" method="GET">

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

</div>

{{-- RINGKASAN --}}
<div class="trial-stats">

    <div class="trial-stat-card">
        <div class="stat-label">TOTAL DEBIT</div>

        <div class="stat-value">
            Rp {{ number_format($totalDebit, 0, ',', '.') }}
        </div>
    </div>

    <div class="trial-stat-card">
        <div class="stat-label">TOTAL KREDIT</div>

        <div class="stat-value">
            Rp {{ number_format($totalKredit, 0, ',', '.') }}
        </div>
    </div>

    <div class="trial-stat-card">
        <div class="stat-label">STATUS</div>

        <div class="stat-value {{ $isBalance ? 'positive' : 'negative' }}">
            {{ $isBalance ? 'BALANCE ✓' : 'TIDAK BALANCE ✗' }}
        </div>
    </div>

</div>

{{-- TABEL NERACA SALDO --}}
<div class="card trial-card">

    <div class="trial-card-heading">
        <h3>Neraca Saldo / Trial Balance</h3>
        <p>Per {{ $period }}</p>
    </div>

    <div class="tbl-wrap">

        <table class="trial-table">

            <thead>
                <tr>
                    <th>No. Akun</th>
                    <th>Nama Akun</th>
                    <th class="num">Debit (Rp)</th>
                    <th class="num">Kredit (Rp)</th>
                </tr>
            </thead>

            <tbody>

                @forelse($neracaSaldo as $row)

                    <tr>
                        <td class="account-code">
                            {{ $row['kode'] }}
                        </td>

                        <td class="acc-name">
                            {{ $row['akun'] }}
                        </td>

                        <td class="debit-val">
                            {{ $row['debit'] > 0
                                ? 'Rp ' . number_format($row['debit'], 0, ',', '.')
                                : '-' }}
                        </td>

                        <td class="kredit-val">
                            {{ $row['kredit'] > 0
                                ? 'Rp ' . number_format($row['kredit'], 0, ',', '.')
                                : '-' }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="trial-empty">
                            Belum ada transaksi sampai periode {{ $period }}.
                        </td>
                    </tr>

                @endforelse

                @if(count($neracaSaldo) > 0)

                    <tr class="trial-total">
                        <td colspan="2">TOTAL</td>

                        <td class="debit-val">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </td>

                        <td class="kredit-val">
                            Rp {{ number_format($totalKredit, 0, ',', '.') }}
                        </td>
                    </tr>

                @endif

            </tbody>

        </table>

    </div>

</div>

@endsection
