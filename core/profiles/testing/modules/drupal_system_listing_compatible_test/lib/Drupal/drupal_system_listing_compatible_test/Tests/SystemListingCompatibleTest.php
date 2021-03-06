<?php

/**
 * @file
 * Definition of Drupal\drupal_system_listing_compatible_test\Tests\SystemListingCompatibleTest.
 */

namespace Drupal\drupal_system_listing_compatible_test\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Helper to verify tests in installation profile modules.
 */
class SystemListingCompatibleTest extends WebTestBase {

  /**
   * Attempt to enable a module from the Testing profile.
   *
   * This test uses the Minimal profile, but enables a module from the Testing
   * profile to confirm that a different profile can be used for running tests.
   *
   * @var array
   */
  public static $modules = array('drupal_system_listing_compatible_test');

  /**
   * Use the Minimal profile.
   *
   * This test needs to use a different installation profile than the test which
   * asserts that this test is found.
   *
   * @see SimpleTestInstallationProfileModuleTestsTestCase
   */
  protected $profile = 'minimal';

  public static function getInfo() {
    return array(
      'name' => 'Installation profile module tests helper',
      'description' => 'Verifies that tests in installation profile modules are found and may use another profile for running tests.',
      'group' => 'Installation profile',
    );
  }

  /**
   * Non-empty test* method required to executed the test case class.
   */
  function testSystemListing() {
    $this->pass(__CLASS__ . ' test executed.');
  }
}
