<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ReembolsoToPorcentajeTransformer implements DataTransformerInterface
{
    private float $costoPorDia;

    public function __construct(float $costoPorDia)
    {
        $this->costoPorDia = $costoPorDia;
    }

    public function transform(mixed $value): mixed
    {
        if ($this->costoPorDia <= 0 || $value === null) {
            return 0;
        }

        return ($value * 100) / $this->costoPorDia;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if ($this->costoPorDia <= 0 || $value === null) {
            return 0;
        }

        return ($value / 100) * $this->costoPorDia;
    }
}