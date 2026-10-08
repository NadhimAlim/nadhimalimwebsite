<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function profileDefaults(): array
    {
        return [
            'hero_intro' => 'Halo, saya Nadhim Alim',
            'hero_title' => 'Menciptakan hal digital yang berkesan.',
            'hero_description' => 'Kreator digital dan web developer yang senang mengubah ide menjadi pengalaman online yang menarik, fungsional, dan punya tujuan.',
            'about_title' => 'Rasa ingin tahu adalah awal dari karya hebat.',
            'about_paragraph_1' => 'Saya percaya karya digital yang baik terasa sederhana untuk digunakan dan berkesan untuk diingat. Saya menggabungkan kreativitas, teknologi, dan perhatian pada detail untuk membuatnya nyata.',
            'about_paragraph_2' => 'Saat tidak sedang membuat sesuatu di layar, saya berbagi proses dan inspirasi dengan komunitas melalui media sosial.',
            'youtube_url' => config('portfolio.youtube_url'),
            'instagram_url' => config('portfolio.instagram_url'),
            'tiktok_url' => config('portfolio.tiktok_url'),
        ];
    }

    public static function profileContent(): array
    {
        $defaults = static::profileDefaults();
        $saved = static::whereIn('key', array_keys($defaults))->pluck('value', 'key')->all();

        return array_replace($defaults, $saved);
    }
}
