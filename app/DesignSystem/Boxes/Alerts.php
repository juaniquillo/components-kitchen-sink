<?php

declare(strict_types=1);

namespace App\DesignSystem\Boxes;

use App\DesignSystem\Boxes\Concerns\IsDesignSystemBox;
use App\DesignSystem\Boxes\Contracts\Box;
use Juaniquillo\BackendDesignSystem\Components\Alert;
use Juaniquillo\BackendDesignSystem\Designs\Design;
use Juaniquillo\BackendDesignSystem\Theme\AlertVariant;

final class Alerts implements Box
{
    use IsDesignSystemBox;

    public const NAME = 'Alerts';

    public function components(Design $design): array
    {
        return [
            Alert::make($design)->variant(AlertVariant::Info)->title('Heads up')->body('Your trial ends in three days.'),
            Alert::make($design)->variant(AlertVariant::Success)->title('Saved')->body('Your changes are live.'),
            Alert::make($design)->variant(AlertVariant::Warning)->title('Careful')->body('This action cannot be undone.'),
            Alert::make($design)->variant(AlertVariant::Danger)->title('Failed')->body('The export did not finish.'),
        ];
    }
}
