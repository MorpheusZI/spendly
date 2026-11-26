@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="container-fluid px-4 px-lg-5 py-4">

    <x-page-header title="Transaksi" subtitle="Catat dan pantau pemasukan serta pengeluaranmu.">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('transactions.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Export PDF
            </a>
            <a href="{{ route('transactions.export.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i>
                Export Excel
            </a>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Tambah transaksi
            </a>
        </div>
    </x-page-header>

    {{-- Filters --}}
        <div class="card transaction-filter-card mb-4">
            <div class="card-body">

                <form action="{{ route('transactions.index') }}" method="GET">

                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="type" class="form-label">
                                Jenis
                            </label>

                            <select name="type" id="type" class="form-select">
                                <option value="">Semua jenis</option>
                                <option value="income" {{ request('type')==='income' ? 'selected' : '' }}>
                                    Pemasukan
                                </option>
                                <option value="expense" {{ request('type')==='expense' ? 'selected' : '' }}>
                                    Pengeluaran
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="category_id" class="form-label">
                                Kategori
                            </label>

                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">Semua kategori</option>

                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id')==$category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                    ({{ $category->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="start_date" class="form-label">
                                Dari tanggal
                            </label>

                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="end_date" class="form-label">
                                Sampai
                            </label>

                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>
                                Filter
                            </button>

                            <a href="{{ route('transactions.index') }}" class="btn btn-light">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    {{-- Transaction history --}}
    <div class="card transaction-list-card">

        <div class="card-body p-0">

            @if ($transactions->count() > 0)

            {{-- Desktop table --}}
            <div class="table-responsive">
                <table id="transactions-table" class="table transaction-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($transactions as $transaction)
                        <tr>
                            <td data-order="{{ $transaction->date->format('Y-m-d') }}" class="text-nowrap">
                                {{ $transaction->date->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="transaction-description">
                                    {{ $transaction->description ?: 'Tanpa deskripsi' }}
                                </div>
                            </td>

                            <td>
                                <span class="category-pill">
                                    {{ $transaction->category->name }}
                                </span>
                            </td>

                            <td>
                                @if ($transaction->type === 'income')
                                <span class="transaction-type income-type">
                                    <i class="bi bi-arrow-up-right"></i>
                                    Pemasukan
                                </span>
                                @else
                                <span class="transaction-type expense-type">
                                    <i class="bi bi-arrow-down-left"></i>
                                    Pengeluaran
                                </span>
                                @endif
                            </td>

                            <td data-order="{{ $transaction->type === 'income' ? (float) $transaction->amount : -(float) $transaction->amount }}"
                                class="text-end text-nowrap">
                                <span
                                    class="{{ $transaction->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format((float)
                                    $transaction->amount, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if ($transaction->receipt_image_path)
                                    <a href="{{ route('transactions.receipt', $transaction) }}"
                                        class="btn btn-sm btn-light" target="_blank" aria-label="Lihat struk"
                                        title="Lihat struk">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('transactions.edit', $transaction) }}"
                                        class="btn btn-sm btn-light" aria-label="Edit transaksi" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-light text-danger"
                                            aria-label="Hapus transaksi" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @else

            <div class="p-4 p-lg-5">
                <x-empty-state icon="bi-receipt" title="Belum ada transaksi"
                    message="Transaksi tidak ditemukan. Coba ubah filter atau tambahkan transaksi baru.">
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah transaksi
                    </a>
                </x-empty-state>
            </div>

            @endif

        </div>


    </div>
    <div class="text-muted small mt-3">
        Total {{ $transactions->count() }} transaksi.
    </div>

</div>
@endsection
