<?php

namespace Drupal\sabc_institution\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a 'SabcInstitutionBlock' block.
 *
 * @Block(
 *  id = "sabc_institution_block",
 *  admin_label = @Translation("Sabc institution block"),
 * )
 */
class SabcInstitutionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['content'] = [
      '#markup' => $this->sabcBlockContent(),
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function schoolDetailedRecords() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $output = build_grants_output();
    if ($output == FALSE) {
      return new JsonResponse("Warning illegal character exists in the institution details, please return back to the results");
    }
    else {
      return new JsonResponse($output);
    }
  }

  /**
   * Designated Institution Lookup.
   *
   * Output institutions to block and format into dataTable.
   */
  public function sabcBlockContent() {

    $alphabetA_Z = range('A', 'Z');

    //$output = '<a href="#" class="full-time" data-toggle="popover" data-original-title="" title="" data-content="&lt;strong&gt;Search tips&lt;/strong&gt;
    //Enter the institution in the search box or use the alphabet to filter by letter.">Search Tips <i class="icon-supportrequest"></i></a>';

    $output =
      '<table id="institution" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Institution</th>
            <th>City</th>
            <th>Province</th>
            <th>Country</th>
            <th>Designated</th>
          </tr>
        </thead>';
    $output .= '<tbody></tbody>';
    $output .= '</table>';

    if($block_name == 'd_Institution'){
			$output = '<div class="paddingR paddingL">
				<div class="box full-width"><div class="heading"><h2>Select the institution you want to attend</h2></div>
				<div class="content">' . $output . '</div>
			</div>';
		}

    return $output;
    // return \Drupal::formBuilder()->getForm('Drupal\sabc_institution\Form\InstitutionForm');

    // $items = \Drupal::moduleHandler()->invokeAll('sabc_institution_block');
    // if (empty($items)) {
    //   return ['#markup' => '<div class="noBlockOutput">' . implode('<br />', $items) . '</div>'];
    // }
    // return [
    //   '#markup' => $output,
    //   '#attached' => [
    //     'library' => ['sabc_institution/sabc_institution'],
    //   ],
    // ];
  }

}
