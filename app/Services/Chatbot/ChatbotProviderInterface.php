<?php

namespace App\Services\Chatbot;

interface ChatbotProviderInterface
{
    public function ask(string $question, array $context = []): string;

    public function name(): string;
}
