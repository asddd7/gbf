<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacterStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GbfCharacterStageController extends Controller
{
    public function index(): View
    {
        $stages = GbfCharacterStage::query()
            ->orderBy('name')
            ->paginate(15);

        return view('admin.stages.index', compact('stages'));
    }

    public function create(): View
    {
        return view('admin.stages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStage($request);

        $validated['stage_key'] = $this->makeUniqueStageKey(
            $validated['name']
        );

        GbfCharacterStage::create($validated);

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil ditambahkan.');
    }

    public function edit(GbfCharacterStage $stage): View
    {
        return view('admin.stages.edit', compact('stage'));
    }

    public function update(
        Request $request,
        GbfCharacterStage $stage
    ): RedirectResponse {
        $stage->update($this->validateStage($request));

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil diperbarui.');
    }

    public function destroy(GbfCharacterStage $stage): RedirectResponse
    {
        $stage->delete();

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil dihapus.');
    }

    private function validateStage(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
    }

    private function makeUniqueStageKey(string $name): string
    {
        $baseKey = Str::limit(Str::slug($name) ?: 'stage', 80, '');
        $stageKey = $baseKey;
        $counter = 1;

        while (GbfCharacterStage::query()->where('stage_key', $stageKey)->exists()) {
            $suffix = '-'.$counter;
            $stageKey = Str::limit(
                $baseKey,
                80 - strlen($suffix),
                ''
            ).$suffix;

            $counter++;
        }

        return $stageKey;
    }
}
