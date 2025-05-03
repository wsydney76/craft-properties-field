<?php

namespace wsydney76\propertiesfield\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use wsydney76\propertiesfield\helpers\PropertiesFieldHelper;

/**
 * Twig extension
 */
class PropertiesFieldExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('propValueSql', function (string $fieldIdent, string $prop, string $cast = '') {
                return PropertiesFieldHelper::propValueSql($fieldIdent, $prop, $cast);
            }),

        ];
    }

}
