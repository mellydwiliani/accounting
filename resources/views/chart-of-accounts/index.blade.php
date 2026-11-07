
@extends('layouts.app')

@section('title', 'Daftar Akun - Accounting')

@section('content')

<!-- ===================================================== -->
<!-- HEADER -->
<!-- ===================================================== -->

<div class="page-header">
    <h1>Daftar Akun</h1>
    <p>Kelola kelompok akun, kategori COA, dan daftar akun perusahaan.</p>
</div>


<!-- ===================================================== -->
<!-- ALERT -->
<!-- ===================================================== -->

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<!-- ===================================================== -->
<!-- 1. MASTER KELOMPOK AKUN -->
<!-- ===================================================== -->

<div class="card">

    <div class="section-header">
        <div>
            <div class="card-title">Master Kelompok Akun</div>
            <p>Kelola kelompok utama akun perusahaan.</p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            onclick="openModal('modal-group-add')"
        >
            + Tambah Kelompok
        </button>
    </div>

    <div class="stats">

        @forelse($groups as $group)

            <div class="stat-card">

                <div class="stat-label">
                    Kode {{ $group->code ?? '-' }}
                </div>

                <div class="stat-value">
                    {{ $group->name }}
                </div>

                <div class="action-buttons">

                    <button
                        type="button"
                        class="btn btn-success btn-sm"
                        onclick="openModal('modal-group-edit-{{ $group->id }}')"
                    >
                        Edit
                    </button>

                    <form
                        action="{{ route('account-groups.destroy', $group->id) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus kelompok {{ $group->name }}?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-sm">
                            Hapus
                        </button>
                    </form>

                </div>

            </div>

        @empty

            <p>Belum ada kelompok akun.</p>

        @endforelse

    </div>

</div>


<!-- MODAL TAMBAH KELOMPOK -->

<div class="modal-overlay" id="modal-group-add">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Tambah Kelompok Akun</h3>

            <button type="button" class="modal-close"
                onclick="closeModal('modal-group-add')">
                &times;
            </button>
        </div>

        <form action="{{ route('account-groups.store') }}" method="POST">

            @csrf

            <div class="form-group">
                <label>Kode Kelompok</label>
                <input
                    type="text"
                    name="code"
                    placeholder="Contoh: 1"
                >
            </div>

            <div class="form-group">
                <label>Nama Kelompok</label>
                <input
                    type="text"
                    name="name"
                    placeholder="Contoh: Aset"
                    required
                >
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="closeModal('modal-group-add')">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Kelompok
                </button>
            </div>

        </form>

    </div>

</div>


<!-- MODAL EDIT KELOMPOK -->

@foreach($groups as $group)

<div class="modal-overlay" id="modal-group-edit-{{ $group->id }}">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Edit Kelompok Akun</h3>

            <button type="button" class="modal-close"
                onclick="closeModal('modal-group-edit-{{ $group->id }}')">
                &times;
            </button>
        </div>

        <form
            action="{{ route('account-groups.update', $group->id) }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Kode Kelompok</label>
                <input
                    type="text"
                    name="code"
                    value="{{ $group->code }}"
                >
            </div>

            <div class="form-group">
                <label>Nama Kelompok</label>
                <input
                    type="text"
                    name="name"
                    value="{{ $group->name }}"
                    required
                >
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="closeModal('modal-group-edit-{{ $group->id }}')">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

</div>

@endforeach


<!-- ===================================================== -->
<!-- 2. MASTER KATEGORI COA -->
<!-- ===================================================== -->

<div class="card">

    <div class="section-header">
        <div>
            <div class="card-title">Master Kategori COA</div>
            <p>Kelola kategori akun dan kelompok induknya.</p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            onclick="openModal('modal-category-add')"
        >
            + Tambah Kategori
        </button>
    </div>

    <div class="tbl-wrap">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Kelompok Akun</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($categories as $category)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <strong>{{ $category->name }}</strong>
                        </td>

                        <td>
                            {{ $category->accountGroup->name ?? '-' }}
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="openModal('modal-category-edit-{{ $category->id }}')"
                                >
                                    Edit
                                </button>

                                <form
                                    action="{{ route('coa-categories.destroy', $category->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->name }}?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4" style="text-align:center;">
                            Belum ada kategori COA.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<!-- MODAL TAMBAH KATEGORI -->

<div class="modal-overlay" id="modal-category-add">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Tambah Kategori COA</h3>

            <button type="button" class="modal-close"
                onclick="closeModal('modal-category-add')">
                &times;
            </button>
        </div>

        <form action="{{ route('coa-categories.store') }}" method="POST">

            @csrf

            <div class="form-group">
                <label>Nama Kategori</label>
                <input
                    type="text"
                    name="name"
                    placeholder="Contoh: Salary"
                    required
                >
            </div>

            <div class="form-group">
                <label>Kelompok Akun</label>

                <select name="account_group_id" required>
                    <option value="">Pilih kelompok akun</option>

                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">
                            {{ $group->code }} - {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="closeModal('modal-category-add')">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Kategori
                </button>
            </div>

        </form>

    </div>

</div>


<!-- MODAL EDIT KATEGORI -->

@foreach($categories as $category)

<div class="modal-overlay" id="modal-category-edit-{{ $category->id }}">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Edit Kategori COA</h3>

            <button type="button" class="modal-close"
                onclick="closeModal('modal-category-edit-{{ $category->id }}')">
                &times;
            </button>
        </div>

        <form
            action="{{ route('coa-categories.update', $category->id) }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama Kategori</label>
                <input
                    type="text"
                    name="name"
                    value="{{ $category->name }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>Kelompok Akun</label>

                <select name="account_group_id" required>

                    @foreach($groups as $group)

                        <option
                            value="{{ $group->id }}"
                            {{ $category->account_group_id == $group->id ? 'selected' : '' }}
                        >
                            {{ $group->code }} - {{ $group->name }}
                        </option>

                    @endforeach

                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="closeModal('modal-category-edit-{{ $category->id }}')">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

</div>

@endforeach


<!-- ===================================================== -->
<!-- 3. TAMBAH AKUN -->
<!-- ===================================================== -->

<div class="card card-sky">

    <div class="card-title">
        Tambah Akun Baru
    </div>

    <form action="{{ route('chart-of-accounts.store') }}" method="POST">

        @csrf

        <div class="stats">

            <div class="form-group">
                <label for="code">Kode Akun</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    placeholder="Contoh: 601"
                    value="{{ old('code') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="name">Nama Akun</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Contoh: Beban Gaji"
                    value="{{ old('name') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category_id">Kategori COA</label>

                <select id="category_id" name="category_id" required>

                    <option value="">Pilih kategori</option>

                    @foreach($categories as $category)

                        <option
                            value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                            ({{ $category->accountGroup->name ?? '-' }})
                        </option>

                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>

                <select id="status" name="status" required>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            + Tambah Akun
        </button>

    </form>

</div>


<!-- ===================================================== -->
<!-- 4. DAFTAR AKUN -->
<!-- ===================================================== -->

<div class="card">

    <div class="card-title">
        Daftar Akun Perusahaan
    </div>

    <div class="tbl-wrap">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Akun</th>
                    <th>Kategori</th>
                    <th>Kelompok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($accounts as $account)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <strong>{{ $account->code }}</strong>
                        </td>

                        <td>{{ $account->name }}</td>

                        <td>
                            {{ $account->category->name ?? '-' }}
                        </td>

                        <td>
                            {{ $account->category->accountGroup->name ?? '-' }}
                        </td>

                        <td>
                            @if($account->status)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Nonaktif</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="openModal('modal-account-edit-{{ $account->id }}')"
                                >
                                    Edit
                                </button>

                                <form
                                    action="{{ route('chart-of-accounts.destroy', $account->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus akun {{ $account->name }}?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" style="text-align:center;">
                            Belum ada data akun.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<!-- ===================================================== -->
<!-- MODAL EDIT AKUN -->
<!-- ===================================================== -->

@foreach($accounts as $account)

<div class="modal-overlay" id="modal-account-edit-{{ $account->id }}">

    <div class="modal-box">

        <div class="modal-header">
            <h3>Edit Akun</h3>

            <button type="button" class="modal-close"
                onclick="closeModal('modal-account-edit-{{ $account->id }}')">
                &times;
            </button>
        </div>

        <form
            action="{{ route('chart-of-accounts.update', $account->id) }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Kode Akun</label>
                <input
                    type="text"
                    name="code"
                    value="{{ $account->code }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>Nama Akun</label>
                <input
                    type="text"
                    name="name"
                    value="{{ $account->name }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>Kategori COA</label>

                <select name="category_id" required>

                    @foreach($categories as $category)

                        <option
                            value="{{ $category->id }}"
                            {{ $account->category_id == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <label>Status</label>

                <select name="status" required>

                    <option value="1" {{ $account->status ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0" {{ !$account->status ? 'selected' : '' }}>
                        Nonaktif
                    </option>

                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    onclick="closeModal('modal-account-edit-{{ $account->id }}')">
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

</div>

@endforeach


@endsection


<!-- ===================================================== -->
<!-- JAVASCRIPT MODAL -->
<!-- ===================================================== -->

@push('scripts')

<script>
    function openModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Klik area luar modal untuk menutup
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // ESC untuk menutup modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
</script>

@endpush
