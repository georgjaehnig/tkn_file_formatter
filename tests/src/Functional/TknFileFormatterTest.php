<?php

namespace Drupal\Tests\tkn_file_formatter\Functional\TknFileFormatterTest;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests the numeric field widget.
 *
 * @group field
 */
class TknFileFormatterTest extends WebDriverTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'field_ui', 'file', 'tkn_file_formatter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();
    // Create an admin user.
    $account = $this->drupalCreateUser([
      'administer content types',
      'administer node fields',
      'administer node display',
      'bypass node access',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Test formatter behavior.
   */
  public function testTknFileFormatter() {

    $type = mb_strtolower($this->randomMachineName());
    $file_field = mb_strtolower($this->randomMachineName());
    $file_size_units = ['B', 'KB', 'MB', 'GB'];
    $prefix = $this->randomMachineName();
    $suffix = $this->randomMachineName();
    $assert_session = $this->assertSession();

    // Create a content type containing float and integer fields.
    $this->drupalCreateContentType(['type' => $type]);

    FieldStorageConfig::create([
      'field_name' => $file_field,
      'entity_type' => 'node',
      'type' => 'file',
    ])->save();

    FieldConfig::create([
      'field_name' => $file_field,
      'entity_type' => 'node',
      'bundle' => $type,
      'settings' => [
        'prefix' => $prefix,
        'suffix' => $suffix,
      ],
    ])->save();

    entity_get_form_display('node', $type, 'default')
      ->setComponent($file_field, [
        'type' => 'file_generic',
      ])
      ->save();

    entity_get_display('node', $type, 'default')
      ->setComponent($file_field, [
        'type' => 'tkn_file_formatter',
      ])
      ->save();

    // Add a file.
    $file = current($this->drupalGetTestFiles('image'));

    // Create file entity.
    $file_entity = File::create([
      'uri' => $file->uri,
      'status' => FILE_STATUS_PERMANENT,
    ]);

    // Create a node to test formatters.
    $node = Node::create([
      'type' => $type,
      'title' => $this->randomMachineName(),
    $file_field => $file_entity, 
  ]);
    $node->save();

    // Go to manage display page.
    $this->drupalGet("admin/structure/types/manage/$type/display");

    // Configure tkn_file_formatter.
    $file_size_unit = $file_size_units[array_rand($file_size_units)];

    $page = $this->getSession()->getPage();
    $page->pressButton("${file_field}_settings_edit");
    $assert_session->waitForElement('css', '.ajax-new-content');

    $edit = [
      "fields[${file_field}][settings_edit_form][settings][file_size_unit]" => $file_size_unit,
    ];
    foreach ($edit as $name => $value) {
      $page->fillField($name, $value);
  }

  $page->pressButton("${file_field}_plugin_settings_update");
  $assert_session->waitForElement('css', '.field-plugin-summary-cell > .ajax-new-content');
  $this->drupalPostForm(NULL, [], t('Save'));

  // Check behavior.
  $this->drupalGet('node/' . $node->id());
  $this->assertRaw($file_size_unit, 'File size unit displayed');
  $this->assertRaw($file_entity->getMimeType(), 'File mime type displayed');
  }
  }
