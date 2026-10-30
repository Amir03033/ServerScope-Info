<?php

namespace App\Support\Markdown;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;

final class RegexPattern implements Pattern
{
    use IsPattern;

    public function __construct(
        private readonly string $regex,
        private readonly TokenType $type,
    ) {}

    public function getPattern(): string
    {
        return $this->regex;
    }

    public function getTokenType(): TokenType
    {
        return $this->type;
    }
}
