<?php

namespace Drupal\Tests\settings_tray\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Base class contains common test functionality for the Settings Tray module.
 */
abstract class SettingsTrayJavascriptTestBase extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function drupalGet($path, array $options = [], array $headers = []) {
    $return = parent::drupalGet($path, $options, $headers);

    // After the page loaded we need to additionally wait until the settings
    // tray Ajax activity is done.
    $this->assertSession()->assertWaitOnAjaxRequest();

    return $return;
  }

  /**
   * Enables a theme.
   *
   * @param string $theme
   *   The theme.
   */
  protected function enableTheme($theme) {
    // Enable the theme.
    \Drupal::service('theme_installer')->install([$theme]);
    $theme_config = \Drupal::configFactory()->getEditable('system.theme');
    $theme_config->set('default', $theme);
    $theme_config->save();
  }

  /**
   * Waits for off-canvas dialog to open.
   */
  protected function waitForOffCanvasToOpen() {
    $web_assert = $this->assertSession();
    $web_assert->assertWaitOnAjaxRequest();
    $this->assertElementVisibleAfterWait('css', '#drupal-off-canvas');
  }

  /**
   * Waits for off-canvas dialog to close.
   */
  protected function waitForOffCanvasToClose() {
    $this->waitForNoElement('#drupal-off-canvas');
  }

  /**
   * Gets the off-canvas dialog element.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   */
  protected function getTray() {
    $tray = $this->getSession()->getPage()->find('css', '.ui-dialog[aria-describedby="drupal-off-canvas"]');
    $this->assertEquals(FALSE, empty($tray), 'The tray was found.');
    return $tray;
  }

  /**
   * Waits for an element to be removed from the page.
   *
   * @param string $selector
   *   CSS selector.
   * @param int $timeout
   *   (optional) Timeout in milliseconds, defaults to 10000.
   */
  protected function waitForNoElement($selector, $timeout = 10000) {
    $condition = "(jQuery('$selector').length == 0)";
    $this->assertJsCondition($condition, $timeout);
  }

  /**
   * Clicks a contextual link.
   *
   * @todo Remove this function when related trait added in
   *   https://www.drupal.org/node/2821724.
   *
   * @param string $selector
   *   The selector for the element that contains the contextual link.
   * @param string $link_locator
   *   The link id, title, or text.
   * @param bool $force_visible
   *   If true then the button will be forced to visible so it can be clicked.
   */
  protected function clickContextualLink($selector, $link_locator, $force_visible = TRUE) {
    if ($force_visible) {
      $this->toggleContextualTriggerVisibility($selector);
    }

    $element = $this->getSession()->getPage()->find('css', $selector);
    $element->find('css', '.contextual button')->press();
    $element->findLink($link_locator)->click();

    if ($force_visible) {
      $this->toggleContextualTriggerVisibility($selector);
    }
  }

  /**
   * Toggles the visibility of a contextual trigger.
   *
   * @todo Remove this function when related trait added in
   *   https://www.drupal.org/node/2821724.
   *
   * @param string $selector
   *   The selector for the element that contains the contextual link.
   */
  protected function toggleContextualTriggerVisibility($selector) {
    // Hovering over the element itself with should be enough, but does not
    // work. Manually remove the visually-hidden class.
    $this->getSession()->executeScript("jQuery('{$selector} .contextual .trigger').toggleClass('visually-hidden');");
  }

  /**
   * Get themes to test.
   *
   * @return string[]
   *   Theme names to test.
   */
  protected function getTestThemes() {
    return ['bartik', 'stark', 'classy', 'stable'];
  }

  /**
   * Asserts the specified selector is visible after a wait.
   *
   * @param string $selector
   *   The selector engine name. See ElementInterface::findAll() for the
   *   supported selectors.
   * @param string|array $locator
   *   The selector locator.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   */
  protected function assertElementVisibleAfterWait($selector, $locator, $timeout = 10000) {
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible($selector, $locator, $timeout));
  }

}
