(function ($, Drupal, once) {
  Drupal.behaviors.regulatoryInformation = {
    attach: function (context, settings) {
      once('regulatoryInformation', 'input[name="regulatory_information__institution_approved_for_title_iv_code"]', context).forEach(function (element) {
        const $titleIvApproval = $(element);
        const $titleIvCode = $('#regulatory_information__title_iv_code', context);
        const $notEligible = $('#regulatory_information__not_eligible', context);
        const $instType = $('#institution_type', context);
        function updateTitleIVFields() {
          const isApproved = $titleIvApproval.filter(':checked').val() === 'Yes';
          if ($instType.val() === 'United States') {
            $('#regulatory_information__institution_is_regulated').hide();
          }
          if ($titleIvApproval.filter(':checked').val() === 'Yes') {
            $titleIvCode.show();
            $notEligible.hide();
            $('#edit-regulatory-information-title-iv-code').attr('required',true);
            $('#regulatory_information__title_iv_code').find('label').addClass('form-required js-form-required');
          }
          else if ($titleIvApproval.filter(':checked').val() === 'No') {
            $titleIvCode.hide();
            $notEligible.show();
          }
        }

        $titleIvApproval.on('change', updateTitleIVFields);
        updateTitleIVFields();
      });
    }
  };
})(jQuery, Drupal, once);

