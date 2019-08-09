<?php

namespace Drupal\tkn_file_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldFormatter;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'tkn_file_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "tkn_file_formatter",
 *   label = @Translation("Taikonauten File formatter"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class TknFileFormatter extends FileFormatterBase
{
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings()
  {
    return array_merge(
      [
        'file_size_unit' => 'KB'
      ],
      parent::defaultSettings()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state)
  {
    $form = parent::settingsForm($form, $form_state);

    $form['file_size_unit'] = [
      '#title' => $this->t('File Size Unit'),
      '#type' => 'select',
      '#options' => [
        'B' => t('Bytes'),
        'KB' => t('Kilobytes'),
        'MB' => t('Megabytes'),
        'GB' => t('Gigabytes')
      ],
      '#default_value' => $this->getSetting('file_size_unit')
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary()
  {
    $summary = [];
    $summary[] = $this->t('File size unit: ') . $this->getSetting('file_size_unit');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode)
  {
    // TODO.
    return [];
  }
}
