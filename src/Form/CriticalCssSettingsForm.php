<?php

namespace Drupal\critical_css\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for critical css.
 */
class CriticalCssSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'critical_css_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['critical_css'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('critical_css.settings');

    $form['critical_css_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => $config->get('enabled'),
      '#description' => t("Enable critical CSS for all anonymous visits. You must manually rebuild Drupal cache when this value changes"),
    ];

    $form['critical_css_help'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('First, generate critical CSS files'),
      '#description' => t("You MUST previously generate a critical CSS file for any bundle type or entity id you want to process. Using <a href=\"https://github.com/addyosmani/critical\" target='_blank'><strong>Addy Osmani's <em>critical</em></strong></a> or <a href=\"https://github.com/filamentgroup/criticalCSS\" target='_blank'><strong>Filament Group's criticalCSS</strong></a> is highly recommended:"),
    ];

    $form['critical_css_help']['gulp'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => t("Addy Osmani's critical gulp task example"),
    ];

    $form['critical_css_help']['gulp']['example'] = [
      '#markup' => '<pre>' . file_get_contents(drupal_get_path('module', 'critical_css') . '/includes/sample-gulp.js') . '</pre>',
    ];

    $form['critical_css_dir_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Critical CSS files base directory (relative to %theme_path)', ['%theme_path' => drupal_get_path('theme', \Drupal::config('system.theme')->get('default'))]),
      '#required' => TRUE,
      '#description' => t('Enter a directory path relative to current theme, where critical CSS files are located (e.g., dist/css/critical). Inside that directory, "Critical CSS" will try to find any file named "{bundle_type}.css" or "{entity_id}.css" (e.g., article.css, page.css, 1234.css etc)'),
      '#default_value' => $config->get('dir_path'),
    ];

    $form['critical_css_excluded_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude entity ids from critical CSS processing'),
      '#required' => FALSE,
      '#description' => t('Enter ids of entities (one per line) which should not be processed. These entities will load standard CSS (synchronously).'),
      '#default_value' => $config->get('excluded_ids'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('critical_css.settings');
    $config
      ->set('enabled', $form_state->getValue('critical_css_enabled'))
      ->set('dir_path', $form_state->getValue('critical_css_dir_path'))
      ->set('excluded_ids', $form_state->getValue('critical_css_excluded_ids'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
