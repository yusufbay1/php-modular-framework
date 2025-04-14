<?php

namespace Core\Functions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomFunctionsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('hotLink', [IFunction::class, 'hotLink']),
            new TwigFunction('revert', [IFunction::class, 'revert']),
            new TwigFunction('assets', [IFunction::class, 'assets']),
            new TwigFunction('base', [IFunction::class, 'base']),
            new TwigFunction('encrypt', [IFunction::class, 'encrypt']),
            new TwigFunction('files', [IFunction::class, 'files']),
            new TwigFunction('encryptUpdate', [IFunction::class, 'encryptUpdate']),
        ];
    }
}


