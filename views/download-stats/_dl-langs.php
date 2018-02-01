<?php
use app\assets\DownloadsPageAsset;
use app\models\Language;
use hiqdev\assets\flagiconcss\FlagIconCssAsset;
use yii\helpers\Html;
use yii\helpers\Url;

FlagIconCssAsset::register($this);
DownloadsPageAsset::register($this);

$langs = Language::find()
  ->with('languageCharsets')
  ->orderBy(['name' => SORT_ASC])
  ->asArray()
  ->all();
?>
<ul class="dl-langs">
<?php foreach ($langs as $lang): ?>
  <li>
    <?= Html::tag(
      'span',
      implode(' ', [
        Html::tag('span', '', ['class' => [
          'flag-icon',
          'flag-icon-' . strtolower(substr($lang['lang'], 3, 2)),
        ]]),
        Html::encode($lang['name']),
      ]),
      ['class' => 'lang']
    ) . "\n" ?>
    <span class="charsets">
<?php foreach ($lang['languageCharsets'] as $_charset): ?>
<?php $charset = $_charset['charset'] ?>
      <span class="charset">
        <?= Html::a(
          trim(implode(' ', [
            $_charset['is_win_acp']
              ? Html::tag('span', '', ['class' => 'fab fa-windows'])
              : '',
            Html::encode($charset['name']),
          ])),
          [$route,
            'lang' => $lang['lang'], 
            'charset' => $charset['php_name'],
          ],
          [
            'hreflang' => $lang['lang'],
            'rel' => 'nofollow',
          ]
        ) . "\n" ?>
      </span>
<?php if ($charset['name'] === 'UTF-8'): ?>
        <span class="charset">
          <?= Html::a(
            Html::encode($charset['name']) . '(BOM)',
            [$route,
              'lang' => $lang['lang'],
              'charset' => $charset['php_name'],
              'bom' => 1,
            ],
            [
              'hreflang' => $lang['lang'],
              'rel' => 'nofollow',
            ]
          ) . "\n" ?>
        </span>
<?php elseif ($charset['name'] === 'UTF-16LE'): ?>
        <span class="charset">
          <?= Html::a(
            Html::encode($charset['name']) . '(TSV)',
            [$route,
              'lang' => $lang['lang'],
              'charset' => $charset['php_name'],
              'tsv' => 1,
            ],
            [
              'hreflang' => $lang['lang'],
              'rel' => 'nofollow',
            ]
          ) . "\n" ?>
        </span>
<?php endif ?>
<?php endforeach ?>
    </span>
  </li>
<?php endforeach ?>
</ul>
