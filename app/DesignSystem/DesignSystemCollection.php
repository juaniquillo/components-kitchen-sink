<?php

declare(strict_types=1);

namespace App\DesignSystem;

use App\DesignSystem\Boxes\Alerts;
use App\DesignSystem\Boxes\Badges;
use App\DesignSystem\Boxes\Buttons;
use App\DesignSystem\Boxes\Cards;
use App\DesignSystem\Boxes\Choices;
use App\DesignSystem\Boxes\Contracts\Box;
use App\DesignSystem\Boxes\DataTable;
use App\DesignSystem\Boxes\Dialog;
use App\DesignSystem\Boxes\Disclosure;
use App\DesignSystem\Boxes\Fields;
use App\DesignSystem\Boxes\Form;
use App\DesignSystem\Boxes\Inputs;
use App\DesignSystem\Boxes\Layout;
use App\DesignSystem\Boxes\Navigation;
use App\DesignSystem\Boxes\SelectAndTextarea;
use App\DesignSystem\Boxes\Tabs;
use App\DesignSystem\Boxes\Typography;

/**
 * The boxes the design system page renders, in the order they appear.
 *
 * The gallery runs in a two-column grid, so this order is visual: boxes are
 * grouped by how tall they render. Reorder freely; the tests assert
 * membership, not sequence.
 */
final class DesignSystemCollection
{
    /**
     * @return array<int, Box>
     */
    public static function boxes(): array
    {
        return [
            new Buttons,
            new Badges,
            new Dialog,
            new Cards,
            new Typography,
            new Layout,
            new Inputs,
            new Choices,
            new SelectAndTextarea,
            new Fields,
            new Form,
            new Disclosure,
            new Navigation,
            new DataTable,
            new Tabs,
            new Alerts,
        ];
    }
}
