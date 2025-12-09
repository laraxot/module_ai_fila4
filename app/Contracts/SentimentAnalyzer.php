<?php

declare(strict_types=1);

namespace Modules\AI\Contracts;

interface SentimentAnalyzer
{
    /**
     * Analizza il sentimento del testo.
     *
     * @param string $text
     * @return array<string,mixed>
     */
    public function analyze(string $text): array;
}
