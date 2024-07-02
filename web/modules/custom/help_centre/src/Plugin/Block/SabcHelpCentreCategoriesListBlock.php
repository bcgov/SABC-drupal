<?php

namespace Drupal\help_centre\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'SabcHelpCentreCategoriesListBlock' block.
 *
 * @Block(
 *  id = "sabc_help_centre_categories_list_block",
 *  admin_label = @Translation("Sabc help centre categories list block"),
 * )
 */
class SabcHelpCentreCategoriesListBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
    public function build() {
      $query = "
			select sabc_taxonomy_term_field_data.name as taxonomy_term_data_name,
			sabc_node_field_data.title as title,
			sabc_node_field_data.nid as nid
			from sabc_taxonomy_term_field_data
			join sabc_node__field_help_section_tags on sabc_node__field_help_section_tags.field_help_section_tags_target_id = sabc_taxonomy_term_field_data.tid
			join sabc_node_field_data on sabc_node_field_data.nid = sabc_node__field_help_section_tags.entity_id
			join sabc_node__field_order_id on sabc_node__field_help_section_tags.entity_id = sabc_node__field_order_id.entity_id
			where sabc_node_field_data.status = :status
			order by sabc_taxonomy_term_field_data.weight asc,
			sabc_node__field_order_id.field_order_id_value desc
		";

      $variables = array(
        ':status' => '1'
      );

      $database = \Drupal::database();
      $result = $database->query($query, $variables);

		$database = \Drupal::database();
		$result = $database->query($query, $variables);

		$category = array();
		$weight = array();

		$results = array();

		foreach ($result as $record) {
			$category[$record->taxonomy_term_data_name][] = array('title' => $record->title, 'nid' => $record->nid);
		}

		foreach($weight as $key => $arr){

			asort($arr);
			$results = array_reverse($arr, true);
			$tmp = array();

			$i=0;
			foreach($results as $k => $v){

				if($i > 5){
					unset($category[$key][$k]);
				}
				else
				{
					$tmp[$key][$k] = $category[$key][$k];
				}

				$i++;
			}

			$category[$key] = $tmp[$key];
		}

		$col1 = null;
		$col2 = null;

		$i=0;

		//BUILD COLUMNS
		foreach($category as $key => $vals){

			$url = strtolower('/help-centre/category/'.str_replace(' ', '-', $key));
			if($i%2 == 0){
				$col1 .= '<strong class="short">'.ucfirst($key) .'</strong>';
				$col1 .= '<ul class="disc">';
			}
			else
			{
				$col2 .= '<strong class="short">'. ucfirst($key) .'</strong>';
				$col2 .= '<ul class="disc">';
			}

			foreach($vals as $item){
			$linkText = $item['title'];
			$url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $item['nid']);
				if($i%2 == 0){
					$col1 .= '<li><a href="'. $url_alias .'">' . $linkText . '</a></li>';
				}
				else
				{
					$col2 .= '<li><a href="'. $url_alias .'">' . $linkText . '</a></li>';
				}
			}

			if($i%2 == 0){
				$col1 .= '</ul> <hr class="small invisible" />';
			}
			else
			{
				$col2 .= '</ul> <hr class="small invisible" />';
			}

			$i++;
		}

		$widget = '<div class="col-md-6">';
		$widget .= $col1;
		$widget .= '</div>';

		$widget .= '<div class="col-md-6">';
		$widget .= $col2;
		$widget .= '</div>';

		//return $widget;
	    return array(
    	  '#markup' => $widget,
    	);
	}
}
