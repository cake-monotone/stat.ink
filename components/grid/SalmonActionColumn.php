<?php

/**
 * @copyright Copyright (C) 2015-2022 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\components\grid;

use LogicException;
use Yii;
use app\components\widgets\EmbedVideo;
use app\components\widgets\FA;
use app\models\Salmon2;
use app\models\Salmon3;
use app\models\User;
use yii\grid\Column;
use yii\helpers\Html;
use yii\helpers\Url;

final class SalmonActionColumn extends Column
{
    public ?User $user = null;

    /**
     * @inheritdoc
     * @param Salmon2|Salmon3 $model
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $user = $this->user ?: $model->user;
        if (!$user) {
            throw new LogicException();
        }

        return \implode(
            ' ',
            \array_filter(
                [
                    Html::a(
                        Html::encode(Yii::t('app', 'Detail')),
                        $this->getDetailUrl($model, $user),
                        [
                            'class' => [
                                'btn',
                                'btn-primary',
                                'btn-xs',
                            ],
                        ],
                    ),
                    $model->link_url
                        ? Html::a(
                            (string)FA::fas(EmbedVideo::isSupported($model->link_url) ? 'video' : 'link')->fw(),
                            $model->link_url,
                            [
                                'class' => [
                                    'btn',
                                    'btn-default',
                                    'btn-xs',
                                ],
                                'rel' => \implode(' ', [
                                    'nofollow',
                                    'noopener',
                                    'noreferrer',
                                ]),
                                'target' => '_blank',
                            ],
                        )
                        : null,
                ],
                fn (?string $content): bool => $content !== null && \trim($content) !== '',
            ),
        );
    }


    /**
     * @param Salmon2|Salmon3 $model
     */
    private function getDetailUrl($model, User $user): array
    {
        switch (\get_class($model)) {
            case Salmon2::class:
                return ['salmon/view',
                    'screen_name' => $user->screen_name,
                    'id' => $model->id,
                ];

            case Salmon3::class:
                return ['salmon-v3/view',
                    'screen_name' => $user->screen_name,
                    'battle' => $model->uuid,
                ];

            default:
                throw new LogicException();
        }
    }
}
