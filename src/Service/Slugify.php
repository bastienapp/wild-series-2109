<?php

namespace App\Service;

class Slugify
{
    public function generate(string $input, string $divider = '-'): string
    {
        // replace non letter or digits by divider
        $slug = preg_replace('~[^\pL\d]+~u', $divider, $input);

        // transliterate
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);

        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        // trim
        $slug = trim($slug, $divider);

        // remove duplicate divider
        $slug = preg_replace('~-+~', $divider, $slug);

        // lowercase
        $slug = strtolower($slug);

        return $slug;
    }
}
