<?php

namespace Drupal\bc_millenium_extras\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
* Provides a 'Recent Records' block.
*
* @Block(
*   id = "recent_records_block",
*   admin_label = @Translation("Recent records block"),
* )
*/
class RecentRecordsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    iform_load_helpers(array('report_helper'));
    $connection = iform_get_connection_details();
    $readAuth = \report_helper::get_read_auth($connection['website_id'], $connection['password']);
    $rows = \report_helper::get_report_data(array(
      'readAuth' => $readAuth,
      'dataSource' => 'specific_surveys/d8_blocks/filterable_explore_list_with_geom',
      'extraParams' => array(
        'smpattrs' => '',
        'occattrs' => '',
        'survey_list' => '101,431',
        'limit' => 10
      ),
      'caching' => true,
      'cacheTimeout' => 60
    ));
    $r = '<div id="recent-records-container">';
    $pointJs = '';
    foreach ($rows as $row) {
      $latin = "<span class=\"latin\">$row[taxon]</span>";
      if ($row['common']) {
        $common = "<span class=\"common\">$row[common]</span>";
        $species = $row['common'] !== $row['taxon'] ?
          "<div class=\"record-title\">$common</div>($latin)" : "<div class=\"record-title\">$latin</div>";
      }
      else
        $species = "<div class=\"record-title\">$latin</div>";
      $r .= '<div class="recent-records-row clearfix">';
      $r .= "<div class=\"recent-records-details\">$species<br/><span class=\"extra\">$row[entered_sref] on $row[date] by $row[recorder]</span></div>";
      if (!empty($row['images'])) {
        $r .= '<div class="recent-records-images">';
        $images = explode(',', $row['images']);
        $class = count($images)>2 ? ' class="multiple"' : '';
        foreach ($images as $image)
          $r .= "<img src=\"http://warehouse1.indicia.org.uk/upload/thumb-$image\" $class>";
        $r .= '</div>';
      }
      $r .= '</div>';
      $pointJs .= "  div.addPt(features, {\"occurrence_id\":\"$row[occurrence_id]\",\"taxon\":\"$row[taxon]\",\"geom\":\"$row[geom]\"}, 'geom', {}, '$row[occurrence_id]');\n";
    }
    $r .= '</div>';
    \report_helper::$javascript .= <<<JS
mapInitialisationHooks.push(function(div) {
  var features = [];
$pointJs
  if (typeof indiciaData.reportlayer==='undefined') {
    var defaultStyle = new OpenLayers.Style(OpenLayers.Util.extend(OpenLayers.Feature.Vector.style['default'], {"strokeColor":"#0000ff","fillColor":"#3333cc","fillOpacity":0.6,"strokeWidth":"\${getstrokewidth}"}), {context: { getstrokewidth: function(feature) {
        var width=feature.geometry.getBounds().right - feature.geometry.getBounds().left,
          strokeWidth=(width===0) ? 1 : 9 - (width / feature.layer.map.getResolution());
        return (strokeWidth<2) ? 2 : strokeWidth;
      } }});
    var selectStyle = new OpenLayers.Style({"strokeColor":"#ff0000","fillColor":"#ff0000","fillOpacity":0.6,"strokeWidth":"\${getstrokewidth}"}, {context: { getstrokewidth: function(feature) {
        var width=feature.geometry.getBounds().right - feature.geometry.getBounds().left,
          strokeWidth=(width===0) ? 1 : 10 - (width / feature.layer.map.getResolution());
        return (strokeWidth<3) ? 3 : strokeWidth;
      } }});
    var styleMap = new OpenLayers.StyleMap({'default' : defaultStyle, 'select' : selectStyle});
    indiciaData.reportlayer = new OpenLayers.Layer.Vector('Report output', {styleMap: styleMap, rendererOptions: {zIndexing: true}});
    div.map.addLayer(indiciaData.reportlayer);
  }
  indiciaData.reportlayer.addFeatures(features);
});
JS;

    return array(
      '#markup' => $r,
      '#cache' => [
        'max-age' => 0,
      ]
    );
  }

}
