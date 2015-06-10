<?php

namespace UIS\Core\Seeders;

use Illuminate\Database\Seeder;
use UIS\Core\Locale\ApplicationLanguage;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $applicationLanguage = ApplicationLanguage::where('application', uis_app_name())
            ->where('show_status', ApplicationLanguage::STATUS_ACTIVE)->first();
        if (!empty($applicationLanguage)) {
            return;
        }
        $languageFile = images_path().DIRECTORY_SEPARATOR.'lng';
        if (!file_exists($languageFile)) {
            mkdir($languageFile);
        }
        $resource = __DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'en.png';
        $languageFile = $languageFile.DIRECTORY_SEPARATOR.'en.png';
        copy($resource, $languageFile);

        $applicationLanguage = new ApplicationLanguage([
            'application' => uis_app_name(),
            'code' => 'en',
            'name' => 'English',
            'icon' => 'en.png',
            'sort_order' => '0',
            'is_default' => ApplicationLanguage::TRUE,
            'show_status' => ApplicationLanguage::STATUS_ACTIVE,
        ]);
        $applicationLanguage->save();
    }
}
