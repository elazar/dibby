<?php

namespace Elazar\Dibby\Template;

use League\Plates\Engine as PlatesEngine;

class PlatesTemplateEngine implements TemplateEngine
{
    public function __construct(
        private PlatesEngine $engine,
    ) { }

    public function render(string $template, array $data = []): string
    {
        return $this->engine->render($template, $data);
    }
}
