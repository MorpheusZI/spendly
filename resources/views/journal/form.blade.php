
@extends('layouts.app')

@section('title', isset($entry) ? 'Edit Jurnal' : 'Jurnal Baru')

@section('content')
<div class="container-fluid px-4 px-lg-5 py-4">
    <div class="mb-4">
        <a href="{{ isset($entry) ? route('journal.show', $entry->id) : route('journal.index') }}"
           class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="mb-4">
        <h1 class="h3 fw-bold">
            {{ isset($entry) ? 'Edit Catatan' : 'Catatan Jurnal Baru' }}
        </h1>
        <p class="text-muted mb-0">
            Tulis refleksi atau rencana keuanganmu.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ isset($entry) ? route('journal.update', $entry->id) : route('journal.store') }}"
                  method="POST">
                @csrf

                @if (isset($entry))
                    @method('PUT')
                @endif

                <div class="mb-4">
                    <label for="title" class="form-label fw-semibold">
                        Judul catatan
                    </label>

                    <input type="text"
                           id="title"
                           name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           maxlength="255"
                           placeholder="Contoh: Evaluasi pengeluaran November"
                           value="{{ old('title', $entry->title ?? '') }}"
                           required>

                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="journal-editor" class="form-label fw-semibold">
                        Isi catatan
                    </label>

                    <textarea id="journal-editor"
                              name="content"
                              class="form-control @error('content') is-invalid @enderror"
                              required>{{ old('content', $entry->content ?? '') }}</textarea>

                    @error('content')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ isset($entry) ? route('journal.show', $entry->id) : route('journal.index') }}"
                       class="btn btn-light">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ isset($entry) ? 'Simpan perubahan' : 'Simpan catatan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

