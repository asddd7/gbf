<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DatabaseStructureController extends Controller
{
    public function index(Request $request): View
    {
        $modules = $this->modules();
        $validated = $request->validate([
            'module' => ['nullable', Rule::in(array_keys($modules))],
        ]);
        $selectedKey = $validated['module'] ?? array_key_first($modules);
        $selectedModule = $modules[$selectedKey];

        $copyText = implode("\n", [
            $selectedModule['title'],
            '',
            'Deskripsi:',
            $selectedModule['description'],
            '',
            'Relasi:',
            ...array_map(
                fn (string $relation): string => '- '.$relation,
                $selectedModule['relations']
            ),
            '',
            'Tabel database:',
            ...array_map(
                fn (string $table): string => '- '.$table,
                $selectedModule['tables']
            ),
            '',
            'File terkait:',
            ...array_map(
                fn (string $file): string => '- '.$file,
                $selectedModule['files']
            ),
        ]);

        return view('admin.database-structure.index', compact(
            'modules',
            'selectedKey',
            'selectedModule',
            'copyText'
        ));
    }

    private function modules(): array
    {
        return [
            'characters' => [
                'title' => 'Characters',
                'description' => 'Mengelola data karakter, termasuk nama, tipe, elemen, deskripsi, gambar, dan urutan. Kebutuhan item karakter mengikuti tahap yang terhubung melalui halaman Progression.',
                'relations' => [
                    'gbf_characters memiliki banyak gbf_character_stages melalui character_id.',
                    'gbf_character_stages dapat belum memiliki karakter (character_id NULL); jika karakter dihapus, tahap menjadi tidak terhubung.',
                    'Kebutuhan item karakter mengikuti relasi tahap karakter, bukan relasi langsung dari karakter ke item.',
                ],
                'tables' => [
                    'gbf_characters',
                    'gbf_character_stages',
                ],
                'files' => [
                    'routes/web.php',
                    'app/Http/Controllers/Admin/GbfCharacterController.php',
                    'app/Models/GbfCharacter.php',
                    'app/Models/GbfCharacterStage.php',
                    'resources/views/admin/characters/index.blade.php',
                    'resources/views/admin/characters/create.blade.php',
                    'resources/views/admin/characters/edit.blade.php',
                    'resources/views/admin/characters/_form.blade.php',
                    'database/migrations/2026_10_30_000000_create_gbf_calculator_catalog_tables.php',
                    'database/migrations/2026_11_03_000000_make_character_stage_character_nullable.php',
                ],
            ],
            'stages' => [
                'title' => 'Tahap Character',
                'description' => 'Mengelola katalog nama tahap dan urutan. Tahap dapat dibuat tanpa karakter; pengaitan tahap ke karakter dilakukan melalui halaman Progression.',
                'relations' => [
                    'gbf_character_stages belongs to one gbf_characters melalui character_id yang nullable.',
                    'gbf_character_stages memiliki banyak gbf_character_stage_materials.',
                    'Jika karakter dihapus, tahap tetap ada dan character_id menjadi NULL; jika tahap dihapus, baris kebutuhan item tahap tersebut ikut terhapus.',
                ],
                'tables' => [
                    'gbf_character_stages',
                    'gbf_characters',
                    'gbf_character_stage_materials',
                ],
                'files' => [
                    'routes/web.php',
                    'app/Http/Controllers/Admin/GbfCharacterStageController.php',
                    'app/Http/Controllers/Admin/GbfCharacterController.php',
                    'app/Http/Controllers/Admin/GbfProgressionController.php',
                    'app/Models/GbfCharacterStage.php',
                    'app/Models/GbfCharacter.php',
                    'app/Models/GbfCharacterStageMaterial.php',
                    'resources/views/admin/stages/index.blade.php',
                    'resources/views/admin/stages/create.blade.php',
                    'resources/views/admin/stages/edit.blade.php',
                    'resources/views/admin/stages/_form.blade.php',
                    'resources/views/admin/characters/_form.blade.php',
                    'resources/views/admin/progression/_form.blade.php',
                    'database/migrations/2026_10_30_000000_create_gbf_calculator_catalog_tables.php',
                    'database/migrations/2026_11_03_000000_make_character_stage_character_nullable.php',
                ],
            ],
            'materials' => [
                'title' => 'Items / Materials',
                'description' => 'Mengelola katalog item/material dan menetapkan kebutuhan item pada tahap karakter, termasuk quantity dan catatan.',
                'relations' => [
                    'gbf_materials memiliki banyak gbf_character_stage_materials.',
                    'gbf_character_stage_materials belongs to one gbf_materials dan satu gbf_character_stages.',
                    'gbf_character_stages dan gbf_materials terhubung many-to-many melalui gbf_character_stage_materials; quantity dan notes disimpan pada tabel penghubung.',
                    'Kombinasi character_stage_id dan material_id harus unik; satu item dapat digunakan pada banyak tahap.',
                ],
                'tables' => [
                    'gbf_materials',
                    'gbf_character_stage_materials',
                    'gbf_character_stages',
                    'gbf_characters',
                ],
                'files' => [
                    'routes/web.php',
                    'app/Http/Controllers/Admin/GbfMaterialController.php',
                    'app/Models/GbfMaterial.php',
                    'app/Models/GbfCharacterStage.php',
                    'app/Models/GbfCharacterStageMaterial.php',
                    'resources/views/admin/materials/index.blade.php',
                    'resources/views/admin/materials/create.blade.php',
                    'resources/views/admin/materials/edit.blade.php',
                    'resources/views/admin/materials/_form.blade.php',
                    'database/migrations/2026_10_30_000000_create_gbf_calculator_catalog_tables.php',
                ],
            ],
            'progression' => [
                'title' => 'Character Progression',
                'description' => 'Mengatur tahap yang dimiliki karakter dan item beserta quantity untuk tiap tahap. Halaman detail menampilkan rangkuman kebutuhan setiap tahap.',
                'relations' => [
                    'gbf_characters memiliki banyak gbf_character_stages melalui character_id.',
                    'gbf_character_stages memiliki banyak gbf_character_stage_materials.',
                    'gbf_character_stage_materials menghubungkan tahap dengan gbf_materials serta menyimpan quantity dan notes.',
                    'Tahap yang dilepas dari progression menjadi tidak terhubung (character_id NULL), tetapi data tahap dan material tahap tetap tersimpan.',
                ],
                'tables' => [
                    'gbf_characters',
                    'gbf_character_stages',
                    'gbf_character_stage_materials',
                    'gbf_materials',
                ],
                'files' => [
                    'routes/web.php',
                    'app/Http/Controllers/Admin/GbfProgressionController.php',
                    'app/Models/GbfCharacter.php',
                    'app/Models/GbfCharacterStage.php',
                    'app/Models/GbfCharacterStageMaterial.php',
                    'app/Models/GbfMaterial.php',
                    'resources/views/admin/progression/index.blade.php',
                    'resources/views/admin/progression/create.blade.php',
                    'resources/views/admin/progression/edit.blade.php',
                    'resources/views/admin/progression/show.blade.php',
                    'resources/views/admin/progression/_form.blade.php',
                    'database/migrations/2026_10_30_000000_create_gbf_calculator_catalog_tables.php',
                    'database/migrations/2026_11_03_000000_make_character_stage_character_nullable.php',
                ],
            ],
            'accounts' => [
                'title' => 'Users & Roles',
                'description' => 'Menyimpan akun pengguna dan peran. Pemeriksaan peran admin digunakan untuk membatasi akses halaman admin.',
                'relations' => [
                    'users belongs to one roles melalui role_id yang nullable.',
                    'roles memiliki banyak users.',
                    'Jika role dihapus, role_id pada pengguna menjadi NULL.',
                ],
                'tables' => [
                    'users',
                    'roles',
                    'password_reset_tokens',
                    'sessions',
                ],
                'files' => [
                    'routes/web.php',
                    'app/Models/User.php',
                    'app/Models/Role.php',
                    'app/Http/Middleware/RoleMiddleware.php',
                    'database/migrations/0001_01_01_000000_create_users_table.php',
                    'database/migrations/2026_11_02_035243_add_role_tables.php',
                    'database/migrations/2026_11_02_040127_add_role_id_to_users_table.php',
                ],
            ],
        ];
    }
}
