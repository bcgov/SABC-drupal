<?php

namespace Drupal\sabc_repayment_rate\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SabcRepaymentRateBlock' block.
 *
 * @Block(
 *  id = "sabc_repayment_rate_block",
 *  admin_label = @Translation("Sabc repayment rate block"),
 * )
 */
class SabcRepaymentRateBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\sabc_repayment_rate\Form\RepaymentCalculatorForm');
    
    return $form;
  }

}
