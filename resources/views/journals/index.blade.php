
@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')

<div class="page-header">
    <h1>📒 Jurnal Umum</h1>
    <p>Input transaksi keuangan satu per satu. Debit harus sama dengan Kredit.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
    </div>
@endif

{{-- FORM TRANSAKSI --}}
<div class="card">
    <div class="card-title">Tambah Transaksi</div>

    <form action="{{ route('journals.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Tanggal</label>
            <input type="date"
                   name="transaction_date"
                   value="{{ old('transaction_date', date('Y-m-d')) }}"
                   required>
        </div>

        <div class="form-group">
            <label>Keterangan Transaksi</label>
            <input type="text"
                   name="description"
                   placeholder="Contoh: Pembayaran biaya sekolah"
                   value="{{ old('description') }}">
        </div>

        <div class="journal-columns">

            {{-- DEBIT --}}
            <div>
                <label class="debit-label">▲ DEBIT</label>

                <div id="debit-list">
                    <div class="journal-row">
                        <select name="debit_coa_id[]" required>
                            <option value="">Pilih akun...</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>

                        <input type="number"
                               name="debit_amount[]"
                               class="debit-amount"
                               placeholder="Jumlah"
                               min="0.01"
                               step="0.01"
                               required>

                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="removeJournalRow(this)">
                            ✕
                        </button>
                    </div>
                </div>

                <button type="button"
                        class="btn btn-ghost btn-sm"
                        onclick="addJournalRow('debit-list', 'debit_coa_id[]', 'debit_amount[]')">
                    + Baris
                </button>
            </div>

            {{-- KREDIT --}}
            <div>
                <label class="credit-label">▼ KREDIT</label>

                <div id="credit-list">
                    <div class="journal-row">
                        <select name="credit_coa_id[]" required>
                            <option value="">Pilih akun...</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>

                        <input type="number"
                               name="credit_amount[]"
                               class="credit-amount"
                               placeholder="Jumlah"
                               min="0.01"
                               step="0.01"
                               required>

                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="removeJournalRow(this)">
                            ✕
                        </button>
                    </div>
                </div>

                <button type="button"
                        class="btn btn-ghost btn-sm"
                        onclick="addJournalRow('credit-list', 'credit_coa_id[]', 'credit_amount[]')">
                    + Baris
                </button>
            </div>
        </div>

        {{-- TOTAL --}}
        <div class="journal-balance">
            <span>Debit: <strong id="total-debit">Rp 0</strong></span>
            <span>Kredit: <strong id="total-credit">Rp 0</strong></span>
            <span id="balance-status"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                + Tambah ke Jurnal
            </button>

            <button type="reset" class="btn btn-ghost">
                Bersihkan
            </button>
        </div>
    </form>
</div>


{{-- DAFTAR JURNAL --}}
<div class="card">

    <div class="journal-heading">
        <strong>Jurnal Umum</strong>
        <span>General Journal</span>
    </div>

    @php
        $journalsByMonth = $journals->groupBy(function ($journal) {
            return \Carbon\Carbon::parse($journal->transaction_date)->format('Y-m');
        });

        $grandDebit = 0;
        $grandCredit = 0;
    @endphp

    <div class="card-title">
        Daftar Transaksi ({{ $journals->count() }} entri)
    </div>

    @forelse($journalsByMonth as $month => $monthJournals)

        @php
            $monthDebit = 0;
            $monthCredit = 0;

            $monthLabel = \Carbon\Carbon::parse($month . '-01')
                ->translatedFormat('F Y');
        @endphp

        {{-- HEADER BULAN --}}
        <div class="journal-month-header">
            <span>{{ strtoupper($monthLabel) }}</span>
            <span>{{ $monthJournals->count() }} transaksi</span>
        </div>

        <div class="tbl-wrap journal-month-table">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Akun</th>
                        <th>Nama Akun</th>
                        <th>Ref</th>
                        <th class="num">Debit (Rp)</th>
                        <th class="num">Kredit (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($monthJournals as $journal)

                        @php
                            $items = $details[$journal->id] ?? collect();
                        @endphp

                        @foreach($items as $index => $item)
                            <tr>
                                @if($index === 0)
                                    <td rowspan="{{ $items->count() }}">
                                        <div class="journal-date">
                                            {{ \Carbon\Carbon::parse($journal->transaction_date)->translatedFormat('d F Y') }}
                                        </div>

                                        @if($journal->description)
                                            <div class="journal-description">
                                                {{ $journal->description }}
                                            </div>
                                        @endif
                                    </td>
                                @endif

                                <td>{{ $item->code }}</td>
                                <td>{{ $item->account_name }}</td>

                                <td>
                                    <span class="badge badge-neutral">JU</span>
                                </td>

                                <td class="num debit-val">
                                    @if($item->debit > 0)
                                        {{ number_format($item->debit, 0, ',', '.') }}
                                    @endif
                                </td>

                                <td class="num credit-val">
                                    @if($item->credit > 0)
                                        {{ number_format($item->credit, 0, ',', '.') }}
                                    @endif
                                </td>

                                @if($index === 0)
                                    <td rowspan="{{ $items->count() }}">
                                        <form action="{{ route('journals.destroy', $journal->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus jurnal ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                ✕
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>

                            @php
                                $monthDebit += $item->debit;
                                $monthCredit += $item->credit;

                                $grandDebit += $item->debit;
                                $grandCredit += $item->credit;
                            @endphp
                        @endforeach

                    @endforeach

                    {{-- TOTAL PER BULAN --}}
                    <tr class="tbl-footer">
                        <td colspan="4">
                            TOTAL {{ strtoupper($monthLabel) }}
                        </td>

                        <td class="num debit-val">
                            {{ number_format($monthDebit, 0, ',', '.') }}
                        </td>

                        <td class="num credit-val">
                            {{ number_format($monthCredit, 0, ',', '.') }}
                        </td>

                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

    @empty
        <div class="text-center">
            Belum ada transaksi jurnal.
        </div>
    @endforelse

    {{-- TOTAL KESELURUHAN --}}
    <div class="tbl-wrap">
        <table>
            <tbody>
                <tr class="tbl-footer">
                    <td colspan="4">TOTAL KESELURUHAN</td>

                    <td class="num debit-val">
                        {{ number_format($grandDebit, 0, ',', '.') }}
                    </td>

                    <td class="num credit-val">
                        {{ number_format($grandCredit, 0, ',', '.') }}
                    </td>

                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('scripts')
<script>
function addJournalRow(listId, accountName, amountName) {
    const list = document.getElementById(listId);
    const firstRow = list.querySelector('.journal-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelector('select').selectedIndex = 0;
    newRow.querySelector('input').value = '';

    list.appendChild(newRow);
}

function removeJournalRow(button) {
    const list = button.closest('[id$="-list"]');

    if (list.querySelectorAll('.journal-row').length > 1) {
        button.closest('.journal-row').remove();
    } else {
        list.querySelector('select').selectedIndex = 0;
        list.querySelector('input').value = '';
    }

    calculateBalance();
}

function calculateBalance() {
    let debit = 0;
    let credit = 0;

    document.querySelectorAll('.debit-amount').forEach(input => {
        debit += Number(input.value || 0);
    });

    document.querySelectorAll('.credit-amount').forEach(input => {
        credit += Number(input.value || 0);
    });

    const format = value =>
        'Rp ' + value.toLocaleString('id-ID');

    document.getElementById('total-debit').textContent = format(debit);
    document.getElementById('total-credit').textContent = format(credit);

    const status = document.getElementById('balance-status');

    if (debit === 0 && credit === 0) {
        status.textContent = '';
    } else if (Math.abs(debit - credit) < 0.01) {
        status.textContent = '✓ Balance!';
        status.style.color = '#16a34a';
    } else {
        status.textContent = '✗ Selisih ' + format(Math.abs(debit - credit));
        status.style.color = '#dc2626';
    }
}

document.addEventListener('input', function(event) {
    if (event.target.matches('.debit-amount, .credit-amount')) {
        calculateBalance();
    }
});
</script>
@endpush
