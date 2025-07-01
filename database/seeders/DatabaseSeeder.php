<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\FileType;
use App\Models\FileExtention;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionTableSeeder::class,
            CreateAdminUserSeeder::class,
        ]);

        $roles = Role::where('name', '!=', 'Super Admin')->get();
        User::factory(100)->create()->each(function ($user) use ($roles) {
            $user->assignRole($roles->random());
        });

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $testUser->assignRole('Visualizzatore');

        $fileTypes = [
            ['name' => 'Documents', 'svg_path' => 'assets/images/iconDocument.svg'],
            ['name' => 'Spreadsheets', 'svg_path' => 'assets/images/iconSpreadsheet.svg'],
            ['name' => 'Presentations', 'svg_path' => 'assets/images/iconPowerPoints.svg'],
            ['name' => 'Images', 'svg_path' => 'assets/images/iconPhotos.svg'],
            ['name' => 'PDFs', 'svg_path' => 'assets/images/iconPDF.svg'],
            ['name' => 'Videos', 'svg_path' => 'assets/images/iconVideos.svg'],
            ['name' => 'Audios', 'svg_path' => 'assets/images/iconAudio.svg'],
            ['name' => 'Others', 'svg_path' => 'assets/images/iconZip.svg'],
            ['name' => 'Folder', 'svg_path' => 'assets/images/iconFolder.svg'],
        ];

        foreach ($fileTypes as $fileType) {
            FileType::create([
                'name' => $fileType['name'],
                'svg_path' => $fileType['svg_path'],
            ]);
        }

        $fileExtensions = [
            // Documents
            ['name' => 'doc', 'file_type_id' => 1, 'svg_path' => 'assets/images/doc.svg'],
            ['name' => 'docx', 'file_type_id' => 1, 'svg_path' => 'assets/images/docx.svg'],
            ['name' => 'pages', 'file_type_id' => 1, 'svg_path' => 'assets/images/pages.svg'],
            ['name' => 'txt', 'file_type_id' => 1, 'svg_path' => 'assets/images/txt.svg'],
            ['name' => 'html', 'file_type_id' => 1, 'svg_path' => 'assets/images/html.svg'],
            ['name' => 'xml', 'file_type_id' => 1, 'svg_path' => 'assets/images/xml.svg'],
            ['name' => 'json', 'file_type_id' => 1, 'svg_path' => 'assets/images/json.svg'],
            ['name' => 'js', 'file_type_id' => 1, 'svg_path' => 'assets/images/js.svg'],
            ['name' => 'css', 'file_type_id' => 1, 'svg_path' => 'assets/images/css.svg'],
            ['name' => 'php', 'file_type_id' => 1, 'svg_path' => 'assets/images/php.svg'],
            ['name' => 'odt', 'file_type_id' => 1, 'svg_path' => 'assets/images/odt.svg'],
            ['name' => 'rtf', 'file_type_id' => 1, 'svg_path' => 'assets/images/rtf.svg'],
            ['name' => 'md', 'file_type_id' => 1, 'svg_path' => 'assets/images/md.svg'],

            // Spreadsheets
            ['name' => 'xls', 'file_type_id' => 2, 'svg_path' => 'assets/images/xls.svg'],
            ['name' => 'xlsx', 'file_type_id' => 2, 'svg_path' => 'assets/images/xlsx.svg'],
            ['name' => 'csv', 'file_type_id' => 2, 'svg_path' => 'assets/images/csv.svg'],
            ['name' => 'ods', 'file_type_id' => 2, 'svg_path' => 'assets/images/ods.svg'],
            ['name' => 'tsv', 'file_type_id' => 2, 'svg_path' => 'assets/images/tsv.svg'],

            // Presentations
            ['name' => 'ppt', 'file_type_id' => 3, 'svg_path' => 'assets/images/ppt.svg'],
            ['name' => 'pptx', 'file_type_id' => 3, 'svg_path' => 'assets/images/pptx.svg'],
            ['name' => 'key', 'file_type_id' => 3, 'svg_path' => 'assets/images/key.svg'],
            ['name' => 'odp', 'file_type_id' => 3, 'svg_path' => 'assets/images/odp.svg'],
            ['name' => 'pps', 'file_type_id' => 3, 'svg_path' => 'assets/images/pps.svg'],

            // Images
            ['name' => 'jpg', 'file_type_id' => 4, 'svg_path' => 'assets/images/jpg.svg'],
            ['name' => 'jpeg', 'file_type_id' => 4, 'svg_path' => 'assets/images/jpeg.svg'],
            ['name' => 'png', 'file_type_id' => 4, 'svg_path' => 'assets/images/png.svg'],
            ['name' => 'gif', 'file_type_id' => 4, 'svg_path' => 'assets/images/gif.svg'],
            ['name' => 'bmp', 'file_type_id' => 4, 'svg_path' => 'assets/images/bmp.svg'],
            ['name' => 'svg', 'file_type_id' => 4, 'svg_path' => 'assets/images/svg.svg'],
            ['name' => 'tiff', 'file_type_id' => 4, 'svg_path' => 'assets/images/tiff.svg'],
            ['name' => 'ico', 'file_type_id' => 4, 'svg_path' => 'assets/images/ico.svg'],

            // PDFs
            ['name' => 'pdf', 'file_type_id' => 5, 'svg_path' => 'assets/images/pdf.svg'],

            // Videos
            ['name' => 'mp4', 'file_type_id' => 6, 'svg_path' => 'assets/images/mp4.svg'],
            ['name' => 'avi', 'file_type_id' => 6, 'svg_path' => 'assets/images/avi.svg'],
            ['name' => 'mov', 'file_type_id' => 6, 'svg_path' => 'assets/images/mov.svg'],
            ['name' => 'mkv', 'file_type_id' => 6, 'svg_path' => 'assets/images/mkv.svg'],
            ['name' => 'flv', 'file_type_id' => 6, 'svg_path' => 'assets/images/flv.svg'],
            ['name' => 'wmv', 'file_type_id' => 6, 'svg_path' => 'assets/images/wmv.svg'],
            ['name' => 'webm', 'file_type_id' => 6, 'svg_path' => 'assets/images/webm.svg'],

            // Audios
            ['name' => 'mp3', 'file_type_id' => 7, 'svg_path' => 'assets/images/mp3.svg'],
            ['name' => 'wav', 'file_type_id' => 7, 'svg_path' => 'assets/images/wav.svg'],
            ['name' => 'flac', 'file_type_id' => 7, 'svg_path' => 'assets/images/flac.svg'],
            ['name' => 'aac', 'file_type_id' => 7, 'svg_path' => 'assets/images/aac.svg'],
            ['name' => 'ogg', 'file_type_id' => 7, 'svg_path' => 'assets/images/ogg.svg'],
            ['name' => 'wma', 'file_type_id' => 7, 'svg_path' => 'assets/images/wma.svg'],

            // Others
            ['name' => 'zip', 'file_type_id' => 8, 'svg_path' => 'assets/images/zip.svg'],
            ['name' => 'rar', 'file_type_id' => 8, 'svg_path' => 'assets/images/rar.svg'],
            ['name' => '7z', 'file_type_id' => 8, 'svg_path' => 'assets/images/7z.svg'],
            ['name' => 'tar', 'file_type_id' => 8, 'svg_path' => 'assets/images/tar.svg'],
            ['name' => 'gz', 'file_type_id' => 8, 'svg_path' => 'assets/images/gz.svg'],
            ['name' => 'bz2', 'file_type_id' => 8, 'svg_path' => 'assets/images/bz2.svg'],
        ];

        foreach ($fileExtensions as $fileExtension) {
            FileExtention::create([
                'name' => $fileExtension['name'],
                'file_type_id' => $fileExtension['file_type_id'],
                'svg_path' => $fileExtension['svg_path'],
            ]);
        }
    }
}
