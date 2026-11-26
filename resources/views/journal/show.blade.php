
@extends('layouts.app')

@section('title', $entry->title)

@section('content')
<div class="container-fluid px-4 px-lg-5 py-4">
    <div class="mb-4">
        <a href="{{ route('journal.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke jurnal
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-2">{{ $entry->title }}</h1>
                    <div class="text-muted small">
                        Dibuat {{ $entry->created_at->format('d M Y, H:i') }}
                        @if ($entry->updated_at->ne($entry->created_at))
                            · Diperbarui {{ $entry->updated_at->format('d M Y, H:i') }}
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('journal.edit', $entry->id) }}"
                       class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>
                        Edit
                    </a>

                    <form action="{{ route('journal.destroy', $entry->id) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

            <hr>

            <article class="journal-content">
                {!! $entry->content !!}
            </article>
        </div>
    </div>
</div>
@endsection

