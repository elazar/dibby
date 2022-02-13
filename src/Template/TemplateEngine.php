<?php

namespace Elazar\Dibby\Template;

interface TemplateEngine
{
    public function render(string $template, array $data = []): string;
}
