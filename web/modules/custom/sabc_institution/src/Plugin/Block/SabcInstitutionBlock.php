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

    return $output;
  }

}
