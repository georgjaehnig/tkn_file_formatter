<?php

namespace Drupal\tkn_file_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Bytes;
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
    $summary[] =
      $this->t('File size unit: ') . $this->getSetting('file_size_unit');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode)
  {
    // Get entity info (to have its caching settings later).
    $entity = $items->getEntity();

    $fileSizeUnit = $this->getSetting('file_size_unit');

    $elements = [];
    foreach ($items as $delta => $item) {
      // Get the file, URL, size, and MIME type.
      $fileId = $item->target_id;
      $file = File::load($fileId);

      // Get the File entity indicated by the file field. If the entity
      // fails to load, something has become disconnected.
      if ($file === null) {
        $url = Url::fromRoute('<none>');
        $fileSize = 0;
        $mime = 'application/octet-stream';
        $filename = $this->t('(Missing file @id)', [
          '@id' => $fileId
        ]);
      } else {
        $url = Url::fromUri(file_create_url($file->getFileUri()));
        $fileSize = $file->getSize();
        $mime = $file->getMimeType();
        $filename = $file->getFilename();
      }

      // Convert according to File Size Unit.
      switch ($fileSizeUnit) {
        case 'GB':
          $fileSizeConverted = $fileSize / pow(Bytes::KILOBYTE, 3);
          break;
        case 'MB':
          $fileSizeConverted = $fileSize / pow(Bytes::KILOBYTE, 2);
          break;
        case 'KB':
          $fileSizeConverted = $fileSize / Bytes::KILOBYTE;
          break;
        default:
          $fileSizeConverted = $fileSize;
          break;
      }

      // Have a rounded number.
      $fileSizeConverted = round($fileSizeConverted);

      // Set element.
      // Wrap in div.
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['tkn-file-formatter'],
        ],
        // Filename in div.
        'filename' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $filename,
          '#attributes' => [
            'class' => ['file filename'],
          ]
        ],
        // Mimetype and Size in another line.
        'mime_and_size' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          'mime' => [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#value' => $mime,
            '#attributes' => [
              'class' => ['mime'],
            ]
          ],
          'size' => [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#value' => ' (' .  $fileSizeConverted .  ' ' .  $fileSizeUnit .  ')',
            '#attributes' => [
              'class' => ['size'],
            ]
          ],
        ],
        '#cache' => [
          'tags' => $entity->getCacheTags()
        ]
      ];
    }
    return $elements;
  }
}
