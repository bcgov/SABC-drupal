<?php

namespace Drupal\sabc_repayment_rate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Class RepaymentCalculatorForm.
 */
class RepaymentCalculatorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'repayment_calculator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['wrapper'] = [
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="row">',
      '#suffix' => '</div>',
    ];
    $form['wrapper']['intructions'] = [
      '#type' => 'markup',
      '#markup' => '<div class="calculator-widget-instructions col-md-6">
      <div class="calculator-widget-instructions-inner">
        <h3 class="short">Instructions</h3>
        <ol class="nice">
          <li>1. Enter the total amount of your loan(s)</li>
          <li>2. Change the prime rate (optional)</li>
          <li>3. Select a fixed or variable rate</li>
          <li>4. Decide on how many years you would like to pay back your loan</li>
        </ol>
        <div class="alert alert-warning"><strong>Note:</strong> This tool is for informational purposes only. Your actual loan repayment amount may vary.</div></div></div>',
      '#weight' => '1',
    ];

    $form['wrapper']['cal'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Loan Repayment Estimator'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#weight' => '0',
      '#attributes' => array('class' => array('col-md-6'), 'id' => array('loan-repayment-estimator'))
    ];
    $form['wrapper']['cal']['loan_amt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Loan amount'),
      '#description' => $this->t('Enter the total amount of your loan(s)'),
      '#weight' => '0',
      '#attributes' => array('class' => array('input-prepend')),
    ];

    $form['wrapper']['cal']['prime_rate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prime rate'),
      '#description' => $this->t('Change the prime rate (optional)'),
      '#default_value' => '2.45',
      '#weight' => '1',
    ];
    $form['wrapper']['cal']['loan_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Loan type'),
      '#description' => $this->t('Select a fixed or variable rate'),
      '#default_value' => 'fixed',
      '#options' => array(
        'fixed' => $this
          ->t('Fixed'),
        'varible' => $this
          ->t('Variable'),
      ),
      '#weight' => '3',
      '#label_classes' => [
        'some-label-class'
      ]
    ];
    $form['wrapper']['cal']['loan_period'] = [
      '#type' => 'select',
      '#title' => $this->t('Loan period (in years)'),
      //'#description' => $this->t('Decide how many years you would like to pay back your loan'),
      '#options' => ['1' => $this->t('1'), '2' => $this->t('2'), '3' => $this->t('3'), '4' => $this->t('4'), '5' => $this->t('5'), '6' => $this->t('6'), '7' => $this->t('7'), '8' => $this->t('8'), '9' => $this->t('9'), '10' => $this->t('10')],
      '#size' => 1,
      '#default_value' => '10',
      '#weight' => '4',
      '#attributes' => array('class' => array('sliderBar'), 'id' => 'loan_period')
    ];
    //$form['#executes_submit_callback'] = FALSE;
    $form['wrapper']['cal']['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Calculate'),
      '#weight' => '5',
      // '#ajax' => [
      //   'callback' => '::setMessage',
      //   'progress' => [
      //     'type' => 'throbber',
      //   ],
      // ]
    ];
    $form['wrapper']['cal']['loan_summary'] = array(
			'#type' => 'markup',
			'#prefix' => '<div id="loan_summary">',
			'#suffix' => '</div>',
      '#weight' => '5',
		);


    // attach js and required js libraries
    $form['#attached']['library'][] = 'system/jquery';
    $form['#attached']['library'][] = 'system/drupal';
    $form['#attached']['library'][] = 'sabc_repayment_rate/sabc_repayment_rate';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateLoan(array &$form, FormStateInterface $form_state) {
    //$response = new AjaxResponse();

	}

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $floating = '2.5';
		$fixed = '5';
    $amt = urldecode(str_replace(',', '', $form_state->getValue('loan_amt')));
		$t = (float)$amt;
		$lt = $form_state->getValue('loan_type');
		$r = (float)$form_state->getValue('prime_rate');
		$y = (float)$form_state->getValue('loan_period');
    $fixed_rate = 2;
    $variable_rate = 0;

    //return $lt;
		//TYPE OF LOAN INTEREST TO USE
		$rt = (float)$form_state->getValue('prime_rate');
    if($lt == "fixed"){
      $rt = $rt + $fixed_rate;
    }else{
      $rt = $rt + $variable_rate;
    }
		$prov_rate = $rt;
		$fed_rate = $rt+2.5;

    //https://hive.aved.gov.bc.ca/jira/projects/SABC/issues/SABC-2640?filter=allopenissues
		//$rt = ($fed_rate*0.6)+($prov_rate*0.4);
    $rt = ($fed_rate*0.6)+($prov_rate*0);

		//CALCULATE MONTHLY INTEREST
		$int_rate = ($rt/100)/12;

		//DETERMINE AMORITIZATION PERIOD (MONTHS)
		$nr_payments = $y*12;

		//GRACE AMORITIZATION PERIOD (MONTHS)
		$g_nr_payments = $y*12-6;

		//CALCULATE GRACE PERIOD INTEREST
		$g = ($t*$int_rate*6);

		//no interest is being applied on grace period starting JAN 2020
    $g = 0;

		//CALCULATE TOTAL LOAN + GRACE PERIOD INTEREST
		$t2 = $t+$g;

		//CALCULATE MONTHLY TOTAL - NO GRACE PERIOD
		$opt1 = ($t*$int_rate*pow((1+$int_rate),$nr_payments))/(pow((1+$int_rate),$nr_payments)-1);

		//CALCULATE MONTHLY TOTAL WITH GRACE PERIOD
		$opt2 = ($t2*$int_rate*pow((1+$int_rate),$g_nr_payments))/(pow((1+$int_rate),$g_nr_payments)-1);

		//CALCULATE MONTHLY TOTAL WITH GRACE PERIOD BUT LUMP INTEREST FOR GRACE PAID OFF
		$opt3 = ($t*$int_rate*pow((1+$int_rate),$g_nr_payments))/(pow((1+$int_rate),$g_nr_payments)-1);

		//TOTAL AMT PAYABLE
		$total_payable1 = $opt1 * $nr_payments;
		$total_payable2 = $opt2 * $g_nr_payments;
		$total_payable3 = $opt3 * $g_nr_payments;

		//TOTAL INTEREST PAYABLE OVER LIFE OF LOAN
		$total_int_payable1 = $total_payable1 - $t;
		$total_int_payable2 = $total_payable2 - $t2;
		$total_int_payable3 = $total_payable3 - $t;

    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '#loan_summary',
        '<div class="accordion" id="accordion2">
        <div class="accordion-group">
          <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
              <div class="repaymentLabel">Start payments after the non-repayment period</div>
              <div class="monthlyTotal">$'.number_format($opt2,2).'/mo</div>
              <div class="clearfix"></div>
            </a>
          </div>
          <div id="collapseOne" class="accordion-body collapse in show">

          <div class="accordion-inner">
            <div class="repaymentLabel">Months to repay loan</div><div class="total">'.$g_nr_payments.'</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Non-repayment period interest</div><div class="total">$'.number_format($g, 2).'</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Loan balance when repayment begins</div><div class="total">$'.number_format($t2, 2).'</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Estimated total interest payable</div><div class="total">$'.number_format($total_int_payable2, 2).'</div>
            <div class="clearfix"></div>
            <div class="totalPayable clearfix">
            <div class="repaymentLabel">Estimated total amount payable</div><div class="total">$'.number_format($total_payable2, 2).'</div>
            </div>
            <div class="clearfix"></div>
            </div>
          </div>


    <div class="accordion-group">
      <div class="accordion-heading">
        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
          <div class="repaymentLabel">Start payments immediately</div>
          <div class="monthlyTotal">$'.number_format($opt1,2).'/mo</div>
          <div class="clearfix"></div>
        </a>
      </div>
      <div id="collapseTwo" class="accordion-body collapse">
      <div class="accordion-inner">
        <div class="repaymentLabel">Months to repay loan</div><div class="total">'.$nr_payments.'</div>
        <div class="clearfix"></div>
        <div class="repaymentLabel">Non-repayment period interest</div><div class="total">$0</div>
        <div class="clearfix"></div>
        <div class="repaymentLabel">Loan balance when repayment begins</div><div class="total">$'.number_format($t, 2).'</div>
        <div class="clearfix"></div>
        <div class="repaymentLabel">Estimated total interest payable</div><div class="total">$'.number_format($total_int_payable1, 2).'</div>
        <div class="clearfix"></div>
        <div class="totalPayable clearfix">
        <div class="repaymentLabel">Estimated total amount payable</div><div class="total">$'.number_format($total_payable1, 2).'</div>
        </div>
        <div class="clearfix"></div>


        </div>


        <div class="accordion-group">
          <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
              <div class="repaymentLabel">Start payments immediately</div>
              <div class="monthlyTotal">$'.number_format($opt1,2).'/mo</div>
              <div class="clearfix"></div>
            </a>
          </div>
          <div id="collapseTwo" class="accordion-body collapse">
          <div class="accordion-inner">
            <div class="repaymentLabel">Months to repay loan</div><div class="total">'.$nr_payments.'</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Non-repayment period interest</div><div class="total">$0</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Loan balance when repayment begins</div><div class="total">$'.number_format($t, 2).'</div>
            <div class="clearfix"></div>
            <div class="repaymentLabel">Estimated total interest payable</div><div class="total">$'.number_format($total_int_payable1, 2).'</div>
            <div class="clearfix"></div>
            <div class="totalPayable clearfix">
            <div class="repaymentLabel">Estimated total amount payable</div><div class="total">$'.number_format($total_payable1, 2).'</div>
            </div>
            <div class="clearfix"></div>
            </div>
          </div>
        </div>'),
    );
    return $response;
   }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


  }
}
