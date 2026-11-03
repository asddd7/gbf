<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
        $unassignedStages = $this->unassignedStages();

        return view('admin.characters.create', compact('unassignedStages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCharacter($request);
        $stages = $validated['stages'] ?? [];
        $unassignedStageIds = $validated['unassigned_stage_ids'] ?? [];
        unset($validated['stages'], $validated['unassigned_stage_ids']);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->uploadCharacterImage(
                $request->file('image'),
                $validated['slug']
            );
        }

        DB::transaction(function () use ($validated, $stages, $unassignedStageIds): void {
            $character = GbfCharacter::create($validated);
            $this->syncStages($character, $stages, $unassignedStageIds);
        });

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Karakter berhasil ditambahkan.');
    }

    public function edit(GbfCharacter $character): View
    {
        $character->load('stages');
        $unassignedStages = $this->unassignedStages();

        return view('admin.characters.edit', compact('character', 'unassignedStages'));
    }

    public function update(
        Request $request,
        GbfCharacter $character
    ): RedirectResponse {
        $validated = $this->validateCharacter($request, $character);
        $stages = $validated['stages'] ?? [];
        $unassignedStageIds = $validated['unassigned_stage_ids'] ?? [];
        unset($validated['stages'], $validated['unassigned_stage_ids']);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $character->id
        );

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->uploadCharacterImage(
                $request->file('image'),
                $validated['slug']
            );
        }

        DB::transaction(function () use (
            $validated,
            $character,
            $stages,
            $unassignedStageIds
        ): void {
            $character->update($validated);
            $this->syncStages($character, $stages, $unassignedStageIds);
        });

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
        $existingStageIds = $character
            ? $character->stages()->pluck('id')->all()
            : [];

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

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],

            'stages' => ['nullable', 'array'],
            'stages.*.id' => [
                'nullable',
                'integer',
                Rule::exists('gbf_character_stages', 'id')
                    ->where('character_id', $character?->id ?? 0),
            ],
            'stages.*.stage_key' => [
                'required',
                'string',
                'max:80',
                'distinct',
                Rule::unique('gbf_character_stages', 'stage_key')
                    ->where(fn (Builder $query) => $query->where(
                        'character_id',
                        $character?->id ?? 0
                    ))
                    ->whereNotIn('id', $existingStageIds),
            ],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.description' => ['nullable', 'string'],
            'stages.*.sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],
            'unassigned_stage_ids' => ['nullable', 'array'],
            'unassigned_stage_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('gbf_character_stages', 'id')
                    ->whereNull('character_id'),
            ],
        ]);
    }

    private function unassignedStages()
    {
        return GbfCharacterStage::query()
            ->whereNull('character_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function syncStages(
        GbfCharacter $character,
        array $stages,
        array $unassignedStageIds = []
    ): void {
        $stageIds = collect($stages)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $unassignedStages = GbfCharacterStage::query()
            ->whereNull('character_id')
            ->whereIn('id', $unassignedStageIds)
            ->get();

        $submittedStageKeys = collect($stages)
            ->pluck('stage_key')
            ->merge($unassignedStages->pluck('stage_key'));

        if ($submittedStageKeys->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'unassigned_stage_ids' => 'Stage key tahap yang dipilih harus unik untuk character ini.',
            ]);
        }

        $conflictingStageKey = GbfCharacterStage::query()
            ->where('character_id', $character->id)
            ->whereNotIn('id', $stageIds)
            ->whereIn('stage_key', $unassignedStages->pluck('stage_key'))
            ->exists();

        if ($conflictingStageKey) {
            throw ValidationException::withMessages([
                'unassigned_stage_ids' => 'Stage key tahap yang dipilih sudah digunakan oleh character ini.',
            ]);
        }

        if ($stageIds === []) {
            $character->stages()->delete();
        } else {
            $character->stages()->whereNotIn('id', $stageIds)->delete();
        }

        foreach ($stages as $stage) {
            $stageId = $stage['id'] ?? null;
            unset($stage['id']);

            if ($stageId) {
                GbfCharacterStage::query()
                    ->where('character_id', $character->id)
                    ->whereKey($stageId)
                    ->update($stage);

                continue;
            }

            $character->stages()->create($stage);
        }

        if ($unassignedStageIds !== []) {
            GbfCharacterStage::query()
                ->whereNull('character_id')
                ->whereIn('id', $unassignedStageIds)
                ->update(['character_id' => $character->id]);
        }
    }

    private function uploadCharacterImage($file, string $slug): string
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw ValidationException::withMessages([
                'image' => 'Konfigurasi Cloudinary belum lengkap.',
            ]);
        }

        $timestamp = time();

        $publicId = 'gbf/characters/' . $slug . '-' . Str::uuid();

        $transformation = 'c_fill,g_auto,h_600,w_600';

        $signatureString =
            'public_id=' . $publicId .
            '&timestamp=' . $timestamp .
            '&transformation=' . $transformation .
            $apiSecret;

        $signature = sha1($signatureString);

        try {
            $response = Http::timeout(30)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                    'api_key' => $apiKey,
                    'timestamp' => $timestamp,
                    'public_id' => $publicId,
                    'transformation' => $transformation,
                    'signature' => $signature,
                ]);

            if (!$response->successful() || !$response->json('secure_url')) {
                Log::error('Cloudinary upload failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                throw ValidationException::withMessages([
                    'image' => 'Gagal mengunggah gambar ke Cloudinary.',
                ]);
            }

            return $response->json('secure_url');

        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Cloudinary connection error', [
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'image' => 'Tidak dapat menghubungi Cloudinary. Silakan coba lagi.',
            ]);
        }
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
