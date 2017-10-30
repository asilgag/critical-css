<?php

namespace Drupal\critical_css\Asset;

use Drupal\Core\Asset\CssCollectionRenderer;
use Drupal\Core\Render\Markup;

/**
 * {@inheritdoc}
 */
class CriticalCssCollectionRenderer extends CssCollectionRenderer {

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {

    // Get parent's output...
    $elements = parent::render($css_assets);

    // Get critical CSS and change parent's output if needed.
    $criticalCss = \Drupal::service('critical_css')->getCriticalCss();
    if ($criticalCss) {
      foreach ($elements as $key => $element) {
        // Add a fallback element at the end of $elements, for non-JS browsers.
        $noScriptElement = $element;
        $noScriptElement['#noscript'] = TRUE;
        $elements[] = $noScriptElement;

        // TODO Find a better way for setting the onload attribute:
        // Due to Drupal escaping quotes inside an attribute,
        // we need to set a dummy "data-onload-rel" attribute, only needed
        // for the onload attribute.
        // 'this.rel="stylesheet"' gets escaped into
        // 'this.rel=&quot;stylesheet&quot;'.
        $elements[$key]['#attributes']['rel'] = 'preload';
        $elements[$key]['#attributes']['as'] = 'style';
        $elements[$key]['#attributes']['data-onload-rel'] = 'stylesheet';
        $elements[$key]['#attributes']['onload'] = 'this.rel=this.dataset.onloadRel';
      }

      // Add Filament Group's loadCSS (https://github.com/filamentgroup/loadCSS)
      $themePath = drupal_get_path('module', 'critical_css');
      $loadCSSContent = file_get_contents($themePath . '/includes/loadCSS/loadCSS.min.js');
      $polyfillContent = file_get_contents($themePath . '/includes/loadCSS/cssrelpreload.min.js');
      $elements[] = [
        '#markup' => Markup::create(
          '<script>' . $loadCSSContent . $polyfillContent . '</script>'
        ),
      ];
    }

    return $elements;
  }

}
