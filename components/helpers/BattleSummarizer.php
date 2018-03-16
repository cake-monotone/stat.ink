<?php
/**
 * @copyright Copyright (C) 2015 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@bouhime.com>
 */

namespace app\components\helpers;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use app\models\Rule2;
use yii\db\Query;

class BattleSummarizer
{
    public static function getSummary(Query $oldQuery)
    {
        $db = Yii::$app->db;
        $now = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        $cond24Hours = sprintf(
            '(({{battle}}.[[end_at]] IS NOT NULL) AND ({{battle}}.[[end_at]] BETWEEN %s AND %s))',
            $db->quoteValue(gmdate('Y-m-d H:i:sO', $now - 86400 + 1)),
            $db->quoteValue(gmdate('Y-m-d H:i:sO', $now))
        );
        $condResultPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle}}.[[is_win]] IS NOT NULL',
        ]));
        $condKDPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle}}.[[kill]] IS NOT NULL',
            '{{battle}}.[[death]] IS NOT NULL',
        ]));
        // ------------------------------------------------------------------------------
        $column_wp = sprintf(
            '(%s * 100.0 / NULLIF(%s, 0))',
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    '{{battle}}.[[is_win]] = TRUE',
                ])
            ),
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                ])
            )
        );
        $column_wp_short = sprintf(
            "(%s * 100.0 / NULLIF(%s, 0))",
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    $cond24Hours,
                    '{{battle}}.[[is_win]] = TRUE',
                ])
            ),
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    $cond24Hours,
                ])
            )
        );
        $column_total_kill = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle}}.[[kill]] ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );
        $column_total_death = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle}}.[[death]] ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );
        $column_kd_present = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );

        $query = clone $oldQuery;
        $query->orderBy(null);
        $query->select([
            'battle_count' => 'COUNT(*)',
            'wp' => $column_wp,
            'wp_short' => $column_wp_short,
            'total_kill' => $column_total_kill,
            'total_death' => $column_total_death,
            'kd_present' => $column_kd_present,
        ]);
        return (object)$query->createCommand()->queryOne();
    }

    public static function getSummary2(Query $oldQuery)
    {
        $db = Yii::$app->db;
        $now = (new DateTimeImmutable())
            ->setTimeZone(new DateTimeZone(Yii::$app->timeZone))
            ->setTimestamp($_SERVER['REQUEST_TIME'] ?? time());
        $cond24Hours = sprintf(
            '(({{battle2}}.[[end_at]] IS NOT NULL) AND ({{battle2}}.[[end_at]] BETWEEN %s AND %s))',
            $db->quoteValue($now->sub(new DateInterval('PT86399S'))->format(DateTime::ATOM)),
            $db->quoteValue($now->format(DateTime::ATOM))
        );
        $condResultPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle2}}.[[is_win]] IS NOT NULL',
        ]));
        $condKDPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle2}}.[[kill]] IS NOT NULL',
            '{{battle2}}.[[death]] IS NOT NULL',
        ]));
        $condSpecialPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle2}}.[[special]] IS NOT NULL',
        ]));
        $condAssistPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle2}}.[[kill_or_assist]] IS NOT NULL',
            '{{battle2}}.[[kill]] IS NOT NULL',
            '{{battle2}}.[[kill_or_assist]] - {{battle2}}.[[kill]] >= 0',
        ]));
        $condInkedPresent = sprintf('(%s)', implode(' AND ', [
            '{{battle2}}.[[is_win]] IS NOT NULL',
            '{{battle2}}.[[my_point]] IS NOT NULL',
            '{{battle2}}.[[rule_id]] IS NOT NULL',
        ]));
        // ------------------------------------------------------------------------------
        $column_wp = sprintf(
            '(%s * 100.0 / NULLIF(%s, 0))',
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    '{{battle2}}.[[is_win]] = TRUE',
                ])
            ),
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                ])
            )
        );
        $column_wp_short = sprintf(
            "(%s * 100.0 / NULLIF(%s, 0))",
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    $cond24Hours,
                    '{{battle2}}.[[is_win]] = TRUE',
                ])
            ),
            sprintf(
                'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
                implode(' AND ', [
                    $condResultPresent,
                    $cond24Hours,
                ])
            )
        );
        $column_battles_short = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condResultPresent,
                $cond24Hours,
                '{{battle2}}.[[is_win]] IS NOT NULL',
            ])
        );
        $column_win_short = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condResultPresent,
                $cond24Hours,
                '{{battle2}}.[[is_win]] = TRUE',
            ])
        );
        $column_total_kill = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle2}}.[[kill]] ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );
        $column_total_death = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle2}}.[[death]] ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );
        $column_kd_present = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condKDPresent,
            ])
        );
        $column_total_specials = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle2}}.[[special]] ELSE 0 END)',
            implode(' AND ', [
                $condSpecialPresent,
            ])
        );
        $column_specials_present = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condSpecialPresent,
            ])
        );
        $assist = sprintf(
            'CASE WHEN (%s) THEN {{battle2}}.[[kill_or_assist]] - {{battle2}}.[[kill]] ELSE NULL END',
            implode(' AND ', [
                $condAssistPresent,
            ])
        );
        $column_total_assists = sprintf(
            'SUM(CASE WHEN (%s) THEN {{battle2}}.[[kill_or_assist]] - {{battle2}}.[[kill]] ELSE 0 END)',
            implode(' AND ', [
                $condAssistPresent,
            ])
        );
        $column_assists_present = sprintf(
            'SUM(CASE WHEN (%s) THEN 1 ELSE 0 END)',
            implode(' AND ', [
                $condAssistPresent,
            ])
        );
        $inked = sprintf('CASE %s END', implode(' ', [
            sprintf(
                'WHEN %s AND {{battle2}}.[[is_win]] = TRUE AND {{battle2}}.[[rule_id]] = %d THEN {{battle2}}.[[my_point]] - 1000',
                implode(' AND ', [$condAssistPresent]),
                Rule2::findOne(['key' => 'nawabari'])->id
            ),
            sprintf(
                'WHEN %s THEN {{battle2}}.[[my_point]]',
                implode(' AND ', [$condAssistPresent])
            ),
            'ELSE NULL',
        ]));
        $column_total_inked = sprintf('SUM(CASE %s END)', implode(' ', [
            sprintf(
                'WHEN %s AND {{battle2}}.[[is_win]] = TRUE AND {{battle2}}.[[rule_id]] = %d THEN {{battle2}}.[[my_point]] - 1000',
                implode(' AND ', [$condAssistPresent]),
                Rule2::findOne(['key' => 'nawabari'])->id
            ),
            sprintf(
                'WHEN %s THEN {{battle2}}.[[my_point]]',
                implode(' AND ', [$condAssistPresent])
            ),
            'ELSE 0',
        ]));
        $column_inked_present = sprintf('SUM(CASE WHEN %s THEN 1 ELSE 0 END)', implode(' AND ', [$condAssistPresent]));

        $query = clone $oldQuery;
        $query->orderBy(null);
        $query->select([
            'battle_count' => 'COUNT(*)',
            'wp' => $column_wp,
            'wp_short' => $column_wp_short,
            'battle_count_short' => $column_battles_short,
            'win_short' => $column_win_short,

            'kd_present' => $column_kd_present,
            'total_kill' => $column_total_kill,
            'total_death' => $column_total_death,

            'min_kill' => 'MIN({{battle2}}.[[kill]])',
            'q1_4_kill' => 'percentile_cont(1.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[kill]])',
            'median_kill' => 'percentile_cont(0.5) WITHIN GROUP (ORDER BY {{battle2}}.[[kill]])',
            'q3_4_kill' => 'percentile_cont(3.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[kill]])',
            'max_kill' => 'MAX({{battle2}}.[[kill]])',
            'stddev_kill' => 'stddev_pop({{battle2}}.[[kill]])',

            'min_death' => 'MIN({{battle2}}.[[death]])',
            'q1_4_death' => 'percentile_cont(1.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[death]])',
            'median_death' => 'percentile_cont(0.5) WITHIN GROUP (ORDER BY {{battle2}}.[[death]])',
            'q3_4_death' => 'percentile_cont(3.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[death]])',
            'max_death' => 'MAX({{battle2}}.[[death]])',
            'stddev_death' => 'stddev_pop({{battle2}}.[[death]])',

            'special_present' => $column_specials_present,
            'total_special' => $column_total_specials,

            'min_special' => 'MIN({{battle2}}.[[special]])',
            'q1_4_special' => 'percentile_cont(1.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[special]])',
            'median_special' => 'percentile_cont(2.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[special]])',
            'q3_4_special' => 'percentile_cont(3.0/4) WITHIN GROUP (ORDER BY {{battle2}}.[[special]])',
            'max_special' => 'MAX({{battle2}}.[[special]])',
            'stddev_special' => 'stddev_pop({{battle2}}.[[special]])',

            'assist_present' => $column_assists_present,
            'total_assist' => $column_total_assists,

            'min_assist' => "MIN({$assist})",
            'q1_4_assist' => "percentile_cont(1.0/4) WITHIN GROUP (ORDER BY {$assist})",
            'median_assist' => "percentile_cont(2.0/4) WITHIN GROUP (ORDER BY {$assist})",
            'q3_4_assist' => "percentile_cont(3.0/4) WITHIN GROUP (ORDER BY {$assist})",
            'max_assist' => "MAX({$assist})",
            'stddev_assist' => "stddev_pop({$assist})",

            'inked_present' => $column_inked_present,
            'total_inked' => $column_total_inked,

            'min_inked' => "MIN({$inked})",
            'q1_4_inked' => "percentile_cont(1.0/4) WITHIN GROUP (ORDER BY {$inked})",
            'median_inked' => "percentile_cont(2.0/4) WITHIN GROUP (ORDER BY {$inked})",
            'q3_4_inked' => "percentile_cont(3.0/4) WITHIN GROUP (ORDER BY {$inked})",
            'max_inked' => "MAX({$inked})",
            'stddev_inked' => "stddev_pop({$inked})",
        ]);
        return (object)$query->createCommand()->queryOne();
    }
}
