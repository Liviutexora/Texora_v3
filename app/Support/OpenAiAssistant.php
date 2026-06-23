<?php

namespace App\Support;

final class OpenAiAssistant
{
    /**
     * True when an OpenAI API key is available (from merged config: settings DB or .env).
     */
    public static function isConfigured(): bool
    {
        return filled(trim((string) (config('services.openai.key') ?? '')));
    }
}
