<?php

/**
 * @file
 * Contains \Drupal\sabc_repayment_rate\Controller\RepayController.
 */

namespace Drupal\sabc_repayment_rate\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use \Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Controller routines for Tool Tips routes.
 */
class RepayController extends ControllerBase {

  /**
   * Returns Glossary Tool Tips.
   *
   * @return array
   *   A render Glossary content
   */
  public function getCalc(Request $request) {
    //MAKE SURE WE HAVE POST VALUES
    	//MAKE SURE WE HAVE POST VALUES
        // get your POST parameter
        $_POST = \Drupal::request()->request->all();
		$response = 'not found';

		if(!empty($_POST)){
			$floating = '2.5';
			$fixed = '5';
			$amt = urldecode(str_replace(',', '', $_POST['loan_amt']));
			$t = (float)$amt;
			$lt = $_POST['loan_type'];
			$r = (float)$_POST['prime_rate'];
			$y = (float)$_POST['loan_period'];
		$fixed_rate = 2;
		$variable_rate = 0;

		//return $lt;
			//TYPE OF LOAN INTEREST TO USE
			$rt = (float)$_POST['prime_rate'];
		if($lt == "fixed"){
		$rt = $rt + $fixed_rate;
		}else{
		$rt = $rt + $variable_rate;
		}
			$prov_rate = $rt;
			$fed_rate = $rt+2.5;

		//https://hive.aved.gov.bc.ca/jira/projects/SABC/issues/SABC-2640?filter=allopenissues
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

		$response =
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
			</div>';
		}
		return new Response($response);
  	}
}
