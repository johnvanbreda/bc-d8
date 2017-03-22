<?php

namespace Drupal\bc_millenium_extras\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a 'Recent Records Map' block.
*
* @Block(
*   id = "recent_records_map_block",
*   admin_label = @Translation("Recent records map block"),
* )
*/
class RecentRecordsMapBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    iform_load_helpers(array('report_helper', 'map_helper'));
    $r = '<div id="recent-records-map-container">';
    $config = \Drupal::config('iform.settings');
    global $indicia_templates;
    $indicia_templates['jsWrap'] = '{content}';
    $r .= \map_helper::map_panel(array(
      'presetLayers' => array('google_streets', 'google_hybrid'),
      'editLayer' => false,
      'initial_lat'=>$config->get('map_centroid_lat'),
      'initial_long'=>$config->get('map_centroid_long'),
      'initial_zoom'=>$config->get('map_zoom'),
      'width'=>'100%',
      'height'=>410,
      'standardControls'=>array('layerSwitcher','panZoomBar')
    ), array('theme' => \map_helper::$js_path . 'theme/default/style.css'));
    $r .= '</div>';
    return array(
      '#markup' => $r,
      '#attached' => array('library' => array(
        'iform/base',
        'iform/jquery',
        'iform/googlemaps',
        'iform/openlayers',
        'iform/jquery_ui',
        'iform/jquery_cookie',
        'iform/indiciaMapPanel'
      )),
      '#cache' => array(
        'max-age' => 0, // disable caching otherwise inline JS is lost
      ),
    );
  }

}
