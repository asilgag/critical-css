<?php

namespace Drupal\critical_css\Service;


/**
 * Gets a node's critical CSS
 */
class CriticalCssService {

  /**
   * Whether Critical CSS has been already processed for this request
   *
   * @var boolean
   */
  protected $alreadyProcessed;


  /**
   * Critical CSS data to be inlined
   *
   * @var string
   */
  protected $css;

  /**
   * {@inheritdoc}
   */
  public function getCriticalCss() {

    // Return previous result, if any
    if ($this->isAlreadyProcessed()) {
      return $this->getCss();
    }

    $this->setAlreadyProcessed(true);

    // Disabled for non-anonymous visits
    if (!\Drupal::currentUser()->isAnonymous()) {
      return null;
    }

    // Check if module is enabled
    if (!$this->isEnabled()) {
      return null;
    }

    $entity = null;
    $entityId = null;
    $bundleName = null;

    // Get current entity's data
    // Try node and taxonomy_term
    $entitiesToTry = ['node', 'taxonomy_term'];
    foreach ($entitiesToTry as $entityToTry) {
      $entity = \Drupal::routeMatch()->getParameter($entityToTry);
      if ($entity) {
        break;
      }
    }

    if ($entity){
      $entityId = $entity->id();
      $bundleName = $entity->bundle();
    } else {
      return null;
    }

    // Check if this entity id is excluded
    if ($this->isEntityIdExcluded($entityId)) {
      return null;
    }

    // Get critical CSS contents by id
    $criticalCssData = $this->getCriticalCssByKey($entityId);
    if ($criticalCssData) {
        return $criticalCssData;
    }

    // Get critical CSS contents by name
    $criticalCssData = $this->getCriticalCssByKey($bundleName);
    if ($criticalCssData) {
      return $criticalCssData;
    }

    return null;

  }

  /**
   * @return boolean
   */
  public function isAlreadyProcessed() {
    return $this->alreadyProcessed;
  }

  /**
   * @param boolean $alreadyProcessed
   */
  protected function setAlreadyProcessed($alreadyProcessed) {
    $this->alreadyProcessed = $alreadyProcessed;
  }

  /**
   * @return string
   */
  public function getCss() {
    return $this->css;
  }

  /**
   * @param string $css
   */
  protected function setCss($css) {
    $this->css = $css;
  }

  /**
   * Check if module is enabled
   *
   * @return boolean
   */
  public function isEnabled() {
    $config = \Drupal::config('critical_css.settings');
    return (bool) $config->get('enabled');
  }

  /**
   * Check if entity id is excluded
   *
   * @param int $entityId
   *
   * @return boolean
   */
  public function isEntityIdExcluded($entityId) {
    $config = \Drupal::config('critical_css.settings');
    $excludedIds = explode("\n", $config->get('excluded_ids'));
    $excludedIds = array_map(function($item) {
      return trim($item);
    }, $excludedIds);
    return (
      is_array($excludedIds) &&
      in_array($entityId, $excludedIds)
    );
  }

  /**
   * Get critical css contents by a key (id, string, etc)
   *
   * @param string $key
   *
   * @return string
   */
  public function getCriticalCssByKey($key) {
    if (empty($key)) {
      return null;
    }

    $themeName = \Drupal::config('system.theme')->get('default');
    $themePath = drupal_get_path('theme', $themeName);
    $criticalCssDirPath = \Drupal::config('critical_css.settings')->get('critical_css_dir_path');
    $criticalCssDir = $themePath.'/'.$criticalCssDirPath;


    $criticalCssFile = $criticalCssDir . '/' . $key . '.css';
    if (!is_file($criticalCssFile)) {
      return null;
    }

    $criticalCssData = file_get_contents($criticalCssFile);
    $this->setCss($criticalCssData);
    return $criticalCssData;
  }

}
