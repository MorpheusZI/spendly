
@extends('layouts.app')

@section('title', 'Jurnal Keuangan')

@section('content')
<div class="container-fluid px-4 px-lg-5 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Jurnal Keuangan</h1>
            <p class="text-muted mb-0">
                Catat refleksi, rencana, dan kebiasaan keuanganmu.
            </p>
        </div>

        <a href="{{ route('journal.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Catatan baru
        </a>
    </div>

    @if ($entries->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-journal-text fs-1 text-muted mb-3"></i>
                <h5>Belum ada catatan jurnal</h5>
                <p class="text-muted">
                    Mulai mencatat perjalanan keuanganmu.
                </p>
                <a href="{{ route('journal.create') }}" class="btn btn-primary">
                    Buat catatan pertama
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($entries as $entry)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <h5 class="fw-bold mb-0">
                                    {{ $entry->title }}
                                </h5>

                                <span class="text-muted small text-nowrap">
                                    {{ $entry->created_at->format('d M Y') }}
                                </span>
                            </div>

                            <div class="text-muted journal-preview">
                                {!! $entry->content !!}
                            </div>

                            <a href="{{ route('journal.show', $entry->id) }}"
                               class="btn btn-outline-primary btn-sm mt-3">
                                Baca catatan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    @endif
</div>
@endsection

