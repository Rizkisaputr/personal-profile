<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'website_title' => 'Rizki Saputra — Full Stack Web Developer',
            'website_description' => 'Personal portfolio of Rizki Saputra, a Full Stack Web Developer focused on practical business applications and reliable digital systems.',
            'seo_title' => 'Rizki Saputra — Full Stack Web Developer',
            'seo_description' => 'Portfolio of Rizki Saputra showcasing full stack web development, business systems, cloud infrastructure, and selected software projects.',
            'footer_text' => 'Building practical digital solutions for real business needs.',
        ];

        foreach ($settings as $key => $value) {
            WebsiteSetting::updateOrCreate(
                compact('key'),
                ['value' => $value, 'type' => 'string'],
            );
        }

        WebsiteSetting::where('key', 'contact_email')->where('value', 'raka@example.com')->update(['value' => null]);
        WebsiteSetting::where('key', 'contact_whatsapp')->where('value', '6281234567890')->update(['value' => null]);
    }
}
