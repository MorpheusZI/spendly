
{{-- Desktop sidebar --}}
<aside class="app-sidebar d-none d-lg-flex">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-icon">
                <i class="bi bi-wallet2"></i>
            </span>
            <span>Spendly</span>
        </a>
    </div>

    <div class="sidebar-user">
        <a href="{{ route('profile.edit') }}" class="sidebar-user-link">
            @if (auth()->user()->profile_photo_path)
                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                     alt="Foto profil"
                     class="user-avatar user-avatar-image">
            @else
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif

            <div class="user-info">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-label">Personal account</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">MENU</span>

        <a href="{{ route('dashboard') }}"
           class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('transactions.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i>
            <span>Transaksi</span>
        </a>

        <a href="{{ route('categories.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i>
            <span>Kategori</span>
        </a>

        <a href="{{ route('reports.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-pdf"></i>
            <span>Laporan</span>
        </a>

        <a href="{{ route('journal.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            <span>Jurnal</span>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="sidebar-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span>Profil</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit" class="sidebar-nav-link logout-link">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

{{-- Mobile bottom navigation --}}
<nav class="mobile-bottom-nav d-lg-none">
    <a href="{{ route('dashboard') }}"
       class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i>
        <span>Home</span>
    </a>

    <a href="{{ route('transactions.index') }}"
       class="mobile-nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
        <i class="bi bi-arrow-left-right"></i>
        <span>Transaksi</span>
    </a>

    <a href="{{ route('categories.index') }}"
       class="mobile-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
        <i class="bi bi-tags"></i>
        <span>Kategori</span>
    </a>

    <a href="{{ route('reports.index') }}"
       class="mobile-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan</span>
    </a>

    <a href="{{ route('journal.index') }}"
       class="mobile-nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
        <span>Jurnal</span>
    </a>

    <a href="{{ route('profile.edit') }}"
       class="mobile-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i>
        <span>Profil</span>
    </a>
</nav>

