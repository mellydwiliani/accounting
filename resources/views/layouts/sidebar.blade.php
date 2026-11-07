
<aside class="sidebar">

    <!-- BRANDING -->
    <div class="sidebar-logo">

        <div class="logo-mark">
            <div class="logo-icon">A</div>
            <span class="logo-text">Accounting</span>
        </div>

        <div class="logo-sub">
            ACCOUNTING SYSTEM
        </div>

    </div>


    <!-- NAVIGATION -->
    <nav class="nav">

        <!-- DASHBOARD -->
        <div class="nav-group">

            <div class="nav-label">
                MENU UTAMA
            </div>

            <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

                <span class="nav-icon">⌂</span>
                Dashboard

            </a>

        </div>


        <!-- MASTER DATA -->
        <div class="nav-group">

            <div class="nav-label">
                MASTER DATA
            </div>

            <a href="{{ Route::has('chart-of-accounts.index') ? route('chart-of-accounts.index') : '#' }}"
               class="{{ request()->routeIs('chart-of-accounts.*') ? 'active' : '' }}">

                <span class="nav-icon">▤</span>
                Daftar Akun

            </a>

        </div>


        <!-- TRANSAKSI -->
        <div class="nav-group">

            <div class="nav-label">
                TRANSAKSI
            </div>

            <a href="#">

                <span class="nav-icon">▧</span>
                Jurnal Umum

            </a>

        </div>


        <!-- LAPORAN -->
        <div class="nav-group">

            <div class="nav-label">
                LAPORAN

            </div>

            <a href="#">

                <span class="nav-icon">▥</span>
                Buku Besar

            </a>

            <a href="#">

                <span class="nav-icon">▦</span>
                Neraca Saldo

            </a>

            <a href="#">

                <span class="nav-icon">▧</span>
                Laporan Keuangan

            </a>

        </div>

    </nav>


    <!-- SIDEBAR BOTTOM -->
    <div class="sidebar-bottom">

        <div class="sidebar-currency">
            Mata Uang: <strong>IDR (Rp)</strong>
        </div>

    </div>

</aside>
