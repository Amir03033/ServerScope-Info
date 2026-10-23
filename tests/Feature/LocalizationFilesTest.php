<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

class LocalizationFilesTest extends TestCase
{
    public function test_dutch_and_english_language_files_have_identical_keys(): void
    {
        foreach (['site', 'changelog'] as $file) {
            $nl = array_keys(Arr::dot(require lang_path("nl/{$file}.php")));
            $en = array_keys(Arr::dot(require lang_path("en/{$file}.php")));

            sort($nl);
            sort($en);

            $this->assertSame($nl, $en, "lang/nl/{$file}.php and lang/en/{$file}.php have different keys.");
        }
    }
}
