<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->user()
            ->journalEntries()
            ->latest()
            ->paginate(10);

        return view('journal.index', compact('entries'));
    }

    public function create()
    {
        return view('journal.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
        ], [
            'title.required' => 'Judul jurnal wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'content.required' => 'Isi jurnal wajib diisi.',
        ]);

        $cleanContent = Purifier::clean($validated['content']);

        if (trim(strip_tags($cleanContent)) === '') {
            return back()
                ->withInput()
                ->withErrors(['content' => 'Isi jurnal tidak boleh kosong.']);
        }

        $request->user()->journalEntries()->create([
            'title' => $validated['title'],
            'content' => $cleanContent,
        ]);

        return redirect()
            ->route('journal.index')
            ->with('success', 'Catatan jurnal berhasil dibuat.');
    }

    public function show(Request $request, string $journal)
    {
        $entry = $request->user()
            ->journalEntries()
            ->findOrFail($journal);

        return view('journal.show', compact('entry'));
    }

    public function edit(Request $request, string $journal)
    {
        $entry = $request->user()
            ->journalEntries()
            ->findOrFail($journal);

        return view('journal.form', compact('entry'));
    }

    public function update(Request $request, string $journal)
    {
        $entry = $request->user()
            ->journalEntries()
            ->findOrFail($journal);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
        ], [
            'title.required' => 'Judul jurnal wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'content.required' => 'Isi jurnal wajib diisi.',
        ]);

        $cleanContent = Purifier::clean($validated['content']);

        if (trim(strip_tags($cleanContent)) === '') {
            return back()
                ->withInput()
                ->withErrors(['content' => 'Isi jurnal tidak boleh kosong.']);
        }

        $entry->update([
            'title' => $validated['title'],
            'content' => $cleanContent,
        ]);

        return redirect()
            ->route('journal.show', $entry->id)
            ->with('success', 'Catatan jurnal berhasil diperbarui.');
    }

    public function destroy(Request $request, string $journal)
    {
        $entry = $request->user()
            ->journalEntries()
            ->findOrFail($journal);

        $entry->delete();

        return redirect()
            ->route('journal.index')
            ->with('success', 'Catatan jurnal berhasil dihapus.');
    }
}

