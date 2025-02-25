<?php

/**
 * @copyright Copyright (C) 2015-2022 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\components\formatters\api\v3;

use yii\base\Model;

final class AliasApiFormatter
{
    public static function toJson(?Model $model, bool $fullTranslate = false): ?string
    {
        return $model && isset($model->key) ? $model->key : null;
    }

    /**
     * @param Model[] $models
     */
    public static function allToJson(array $models, bool $fullTranslate = false): array
    {
        return \array_filter(
            \array_map(
                fn (Model $model): ?string => self::toJson($model, $fullTranslate),
                $models
            ),
            fn (?string $v): bool => $v !== null,
        );
    }
}
