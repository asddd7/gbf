<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GbfMaterialController extends Controller
{
    public function index(): View
    {
        $materials = GbfMaterial::query()
            ->with('stageMaterials.assignment.character')
            ->withCount('stageMaterials')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('admin.materials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaterial($request);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->uploadMaterialImage(
                $request->file('image'),
                $validated['slug']
            );
        }

        GbfMaterial::create($validated);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function edit(GbfMaterial $material): View
    {
        return view('admin.materials.edit', compact('material'));
    }

    public function update(Request $request, GbfMaterial $material): RedirectResponse
    {
        $validated = $this->validateMaterial($request, $material);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $material->id
        );

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->uploadMaterialImage(
                $request->file('image'),
                $validated['slug']
            );
        }

        $material->update($validated);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(GbfMaterial $material): RedirectResponse
    {
        $material->delete();

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }

    private function validateMaterial(
        Request $request,
        ?GbfMaterial $material = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('gbf_materials', 'slug')->ignore($material?->id),
            ],
            'category' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:30'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value) ?: 'material';
        $slug = $baseSlug;
        $counter = 1;

        while (
            GbfMaterial::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function uploadMaterialImage(
        UploadedFile $file,
        string $slug
    ): string {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw ValidationException::withMessages([
                'image' => 'Konfigurasi Cloudinary belum lengkap.',
            ]);
        }

        $timestamp = time();

        $publicId = 'gbf/materials/' . $slug . '-' . Str::uuid();

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
                ->post(
                    "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
                    [
                        'api_key' => $apiKey,
                        'timestamp' => $timestamp,
                        'public_id' => $publicId,
                        'transformation' => $transformation,
                        'signature' => $signature,
                    ]
                );

            if (!$response->successful() || !$response->json('secure_url')) {
                Log::error('Cloudinary material upload failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                throw ValidationException::withMessages([
                    'image' => 'Gagal mengunggah gambar material ke Cloudinary.',
                ]);
            }

            return $response->json('secure_url');

        } catch (ValidationException $exception) {
            throw $exception;

        } catch (\Throwable $exception) {
            Log::error('Cloudinary material connection error', [
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'image' => 'Tidak dapat menghubungi Cloudinary. Silakan coba lagi.',
            ]);
        }
    }
}
