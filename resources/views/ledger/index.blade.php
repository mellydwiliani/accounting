
@extends('layouts.app')

@section('title', 'Buku Besar')

@section('content')

<div class="page-header">
    <div class="company-tag">
        Buku Besar / General Ledger
    </div>

    <h1>📚 Buku Besar</h1>

    <p>Seluruh transaksi yang diposting dari Jurnal Umum.</p>
</div>

<div class="ledger-toolbar">
    <form action="{{ route('ledger.index') }}" method="GET">
        <label for="year">Tahun:</label>

        <select name="year" id="year">
            @foreach($years as $y)
                <option value="{{ $y }}"
                    {{ $year == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endforeach
                
        </select>

        <button type="submit" class="btn-primary">
            Tampilkan
        </button>
    </form>

    <a href="{{ route('journals.index') }}" class="btn-secondary">
        ← Jurnal Umum
    </a>
</div>

@forelse($monthlyLedger as $month => $accountsData)

    <div class="ledger-month">

        <div class="ledger-month-header">
            <h2>
                {{ strtoupper(\Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y')) }}
            </h2>
        </div>

        @foreach($accountsData as $account)

            <div class="ledger-account">

                <div class="ledger-account-header">
                    <h3>
                        {{ $account['account_code'] }}
                        ·
                        {{ $account['account_name'] }}
                    </h3>

                    <span>
                        Saldo Akhir:
                        <strong>
                            Rp {{ number_format(abs($account['closing_balance']), 0, ',', '.') }}
                            {{ $account['closing_balance'] < 0 ? '(K)' : '(D)' }}
                        </strong>
                    </span>
                </div>

                <div class="table-wrapper">
                    <table class="ledger-table">

                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Ref</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr class="ledger-opening">
                                <td colspan="3">
                                    Saldo Awal
                                </td>

                                <td colspan="2">-</td>

                                <td>
                                    Rp {{ number_format(abs($account['opening_balance']), 0, ',', '.') }}
                                    {{ $account['opening_balance'] < 0 ? '(K)' : '(D)' }}
                                </td>
                            </tr>

                            @php
                                $runningBalance = $account['opening_balance'];
                            @endphp

                            @foreach($account['transactions'] as $transaction)

                                @php
                                    $runningBalance += $transaction->debit - $transaction->credit;
                                @endphp

                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->translatedFormat('d M Y') }}
                                    </td>

                                    <td>
                                        {{ $transaction->description ?: $transaction->journal_description }}
                                    </td>

                                    <td>
                                        {{ $transaction->journal_number }}
                                    </td>

                                    <td class="text-green">
                                        {{ $transaction->debit > 0
                                            ? 'Rp ' . number_format($transaction->debit, 0, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td class="text-red">
                                        {{ $transaction->credit > 0
                                            ? 'Rp ' . number_format($transaction->credit, 0, ',', '.')
                                            : '-' }}
                                    </td>

                                    <td>
                                        Rp {{ number_format(abs($runningBalance), 0, ',', '.') }}
                                        {{ $runningBalance < 0 ? '(K)' : '(D)' }}
                                    </td>
                                </tr>

                            @endforeach

                            <tr class="ledger-total">
                                <td colspan="3">
                                    Total Mutasi
                                </td>

                                <td class="text-green">
                                    Rp {{ number_format($account['debit'], 0, ',', '.') }}
                                </td>

                                <td class="text-red">
                                    Rp {{ number_format($account['credit'], 0, ',', '.') }}
                                </td>

                                <td>
                                    Rp {{ number_format(abs($account['closing_balance']), 0, ',', '.') }}
                                    {{ $account['closing_balance'] < 0 ? '(K)' : '(D)' }}
                                </td>
                            </tr>

                        </tbody>

                    </table>
                </div>

            </div>

        @endforeach

    </div>

@empty

    <div class="empty-state">
        <h3>Belum ada transaksi</h3>
        <p>Silakan input transaksi terlebih dahulu melalui Jurnal Umum.</p>

        <a href="{{ route('journals.index') }}">
            Input Jurnal Umum
        </a>
    </div>

@endforelse

@endsection
