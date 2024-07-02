<?php /**
 * @file
 * Contains \Drupal\sabc_institution\Plugin\Block\Grants_Grads.
 */

namespace Drupal\sabc_institution\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the Grants_Grads block.
 *
 * @Block(
 *   id = "sabc_institution_Grants_Grads",
 *   admin_label = @Translation("BC Grants for Grads")
 * )
 */
class Grants_Grads extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /**
     * @FIXME
     * hook_block_view() has been removed in Drupal 8. You should move your
     * block's view logic into this method and delete sabc_institution_block_view()
     * as soon as possible!
     */
    return sabc_institution_block_view('Grants_Grads');
  }

  
}
