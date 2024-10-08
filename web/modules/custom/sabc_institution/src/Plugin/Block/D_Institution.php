<?php /**
 * @file
 * Contains \Drupal\sabc_institution\Plugin\Block\D_Institution.
 */

namespace Drupal\sabc_institution\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides the D_Institution block.
 *
 * @Block(
 *   id = "sabc_institution_d_Institution",
 *   admin_label = @Translation("Dashboard :: Institution Lookup")
 * )
 */
class D_Institution extends BlockBase implements ContainerFactoryPluginInterface {

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

    if($this->getPluginId() === 'sabc_institution_d_Institution'){
			$output = '<div class="paddingR paddingL">
				<div class="box full-width"><div class="heading"><h2>Select the institution you want to attend</h2></div>
				<div class="content">' . $output . '</div>
			</div>';
		}

    return $output;
  }

}
