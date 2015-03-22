<?php

use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use UIS\Core\Models\BaseModel;

class UISCoreLanguageSeeder extends Seeder
{
    /**
     * The Illuminate Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!empty(DB::table('language')->first())) {
            return;
        }

        $languagesJsonFile = __DIR__ . '/resources/uis_core_languages.json';
        $languagesData = json_decode($this->files->get($languagesJsonFile));
        $insertLanguages = [];
        $lngIndex = 0;
        foreach ($languagesData as $lngData) {
            $code = isset($lngData->code) ? $lngData->code : '';
            $name = isset($lngData->name_native) ? $lngData->name_native : '';
            $enName = isset($lngData->name_en) ? $lngData->name_en : '';
            $isDefault = isset($lngData->is_default) && $lngData->is_default ? BaseModel::TRUE : BaseModel::FALSE;

            $insertLanguages[$lngIndex] = [
                'code' => $code,
                'name' => $name,
                'en_name' => $enName,
                'sort_order' => $lngIndex * 10,
                'is_default' => $isDefault,
                'show_status' => BaseModel::STATUS_ACTIVE
            ];
            $lngIndex++;
        }
        DB::table('language')->insert($insertLanguages);
    }
}
