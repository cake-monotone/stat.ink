<?php

/**
 * @copyright Copyright (C) 2015-2022 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\components\formatters\api\v3;

use Yii;

final class TeamColorApiFormatter
{
    public static function toJson(?string $value, bool $fullTranslate): ?string
    {
        return $value;
    }
}
