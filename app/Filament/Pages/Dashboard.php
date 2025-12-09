<?php

declare(strict_types=1);

namespace Modules\AI\Filament\Pages;

use BackedEnum;
use Modules\Xot\Filament\Pages\XotBasePage;

class Dashboard extends XotBasePage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'ai::filament.pages.dashboard';
}
