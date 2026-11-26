@extends('layouts.app')

@section('title', 'Laporan PDF')

@section('content')
<div class="container-fluid px-4 px-lg-5 py-4">

    <x-page-header
        title="Laporan Keuangan"
        subtitle="Download ringkasan dan riwayat keuanganmu dalam PDF."
    />

    <div class="row">
        <div class="col-12 col-lg-7 col-xl-6">

            <div class="card report-card">
                <div class="card-body p-4">

                    <div class="report-icon mb-3">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>

                    <h5 class="mb-2">Export laporan PDF</h5>

                    <p class="text-muted mb-4">
                        Pilih periode laporan yang ingin kamu download.
                    </p>

                    <form
                        action="{{ route('reports.download') }}"
                        method="GET"
                    >

                        {{-- Start date --}}
                        <div class="mb-3">
                            <label for="start_date" class="form-label">
                                Tanggal mulai
                            </label>

                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}"
                                required
                            >

                            @error('start_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- End date --}}
                        <div class="mb-4">
                            <label for="end_date" class="form-label">
                                Tanggal akhir
                            </label>

                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}"
                                required
                            >

                            @error('end_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-download me-2"></i>
                            Download PDF
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection

