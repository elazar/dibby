<?php

namespace Elazar\Dibby;

trait Immutable
{
    private function with(string $property, mixed $value): static
    {
        $clone = clone $this;
        $clone->$property = $value;
        return $clone;
    }
}
