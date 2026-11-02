<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GbfCharacterController extends Controller
{
    public function index(): View
    {
        $characters = GbfCharacter::query()
            ->withCount('stages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.characters.index', compact('characters'));
    }

    public function create(): View
    {
        return view('admin.characters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCharacter($request);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        GbfCharacter::create($validated);

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Karakter berhasil ditambahkan.');
    }

    public function edit(GbfCharacter $character): View
    {
        return view('admin.characters.edit', compact('character'));
    }

    public function update(
        Request $request,
        GbfCharacter $character
    ): RedirectResponse {
        $validated = $this->validateCharacter($request, $character);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $character->id
        );

        $character->update($validated);

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Karakter berhasil diperbarui.');
    }

    public function destroy(GbfCharacter $character): RedirectResponse
    {
        $character->delete();

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Karakter berhasil dihapus.');
    }

    private function validateCharacter(
        Request $request,
        ?GbfCharacter $character = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('gbf_characters', 'slug')
                    ->ignore($character?->id),
            ],

            'character_type' => [
                'required',
                Rule::in(['eternal', 'evoker']),
            ],

            'element' => [
                'nullable',
                'string',
                'max:20',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image_path' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],
        ]);
    }

    private function makeUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while (
            GbfCharacter::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
