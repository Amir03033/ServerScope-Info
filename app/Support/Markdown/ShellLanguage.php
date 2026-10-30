<?php

namespace App\Support\Markdown;

use Tempest\Highlight\Language;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Minimal shell highlighting (tempest/highlight 2.12 has none): commands,
 * flags, variables, strings and comments. No injections, so the code is
 * never altered, only wrapped in spans.
 */
final class ShellLanguage implements Language
{
    public function getName(): string
    {
        return 'sh';
    }

    public function getAliases(): array
    {
        return ['bash', 'shell', 'console', 'zsh'];
    }

    public function getInjections(): array
    {
        return [];
    }

    public function getPatterns(): array
    {
        return [
            // First word of a line, also after sudo / && / | / ;
            new RegexPattern('/(?:^|&&|\|\||[|;]|(?<=\bsudo))[ \t]*(?<match>[a-zA-Z_][\w.\/-]*)(?=[ \t]|$)/m', TokenTypeEnum::KEYWORD),
            new RegexPattern('/(?<=[ \t])(?<match>--?[a-zA-Z][\w-]*)/', TokenTypeEnum::PROPERTY),
            new RegexPattern('/(?<match>\$\{?[A-Za-z_]\w*\}?)/', TokenTypeEnum::VARIABLE),
            new RegexPattern('/(?<match>"[^"\n]*"|\'[^\'\n]*\')/', TokenTypeEnum::VALUE),
            new RegexPattern('/(?:^|[ \t])(?<match>#[^\n]*)/m', TokenTypeEnum::COMMENT),
        ];
    }
}
