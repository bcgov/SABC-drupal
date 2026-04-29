document.addEventListener("DOMContentLoaded", function(event) {

    var formattedInsPhone = new Formatter(document.getElementById('institution_phone_number'), {
        'pattern': '{{9999999999999}}',
    });
    var formattedInstFax = new Formatter(document.getElementById('institution_fax_number'), {
        'pattern': '{{9999999999999}}',
    });
    var formattedInstFax = new Formatter(document.getElementById('institution_contact_legal_phone'), {
        'pattern': '{{9999999999999}}',
    });
    var formattedInstFax = new Formatter(document.getElementById('institution_contact_legal_phone2'), {
        'pattern': '{{9999999999999}}',
    });
    var formattedTotalWeeks = new Formatter(document.getElementById('program_information_total_weeks'), {
        'pattern': '{{999}}',
    });
    var formattedAverageHours = new Formatter(document.getElementById('program_information_average_hours'), {
        'pattern': '{{999}}',
    });
    var formattedMinAge = new Formatter(document.getElementById('program_information_admission_requirements_extra'), {
        'pattern': '{{99}}',
    });
    var formattedPtipCode = new Formatter(document.getElementById('regulatory_information__bc_private_regulatory_body_ptip_code'), {
        'pattern': '{{99999}}',
    });

    var formattedPractice1 = new Formatter(document.getElementById('program_information_type_practice1'), {
        'pattern': '{{999}}',
    });

    var formattedPractice3 = new Formatter(document.getElementById('program_information_type_practice3'), {
        'pattern': '{{999}}',
    });
    var formattedPractice4 = new Formatter(document.getElementById('program_information_type_practice4'), {
        'pattern': '{{999}}',
    });
    var formattedTotalHours = new Formatter(document.getElementById('total_hours_study_program'), {
        'pattern': '{{9999}}',
    });
    var formattedHoursPerYear = new Formatter(document.getElementById('total_hours_study_per_year'), {
        'pattern': '{{9999}}',
    });
    var formattedActualTution = new Formatter(document.getElementById('actual_tution'), {
        'pattern': '{{999999}}',
    });
    var formattedMandatoryFees = new Formatter(document.getElementById('mandatory_fees'), {
        'pattern': '{{999999}}',
    });
    var formattedRelatedCosts = new Formatter(document.getElementById('program_related_costs'), {
        'pattern': '{{999999}}',
    });
    var formattedExceptionalExpenses = new Formatter(document.getElementById('exceptional_expenses'), {
        'pattern': '{{999999}}',
    });




    //jQuery("#institution_information").show();
    //jQuery("#regulatory_information").hide();

    jQuery("#program_not_eligible8").hide();
    jQuery("#program_not_eligible8_1").hide();
    jQuery("#apply_all_program_dates_container").hide();
    jQuery("#regulatory_information__international_institution_us_dept_edu_iv_title").hide();

    jQuery("#institution_sabc_code").hide();
    jQuery("#regulatory_information_bc").hide();
    jQuery("#regulatory_information_out_province").hide();
    jQuery("#regulatory_information_out_province input[type='radio']").attr('disabled', true);

    jQuery("#regulatory_information_us").hide();
    jQuery("#regulatory_information_international").hide();
    jQuery("#regulatory_information_international input[type='radio']").attr('disabled', true);

    jQuery("#regulatory_information_international_medical").hide();
    jQuery("#regulatory_information_international_medical input[type='radio']").attr('disabled', true);


    if(jQuery("#institution_type").val() != "B.C. Private"){
      jQuery("#education_costs").hide();
      jQuery("#education_costs input").each(function(){
        jQuery(this).removeAttr('required');
      });
      jQuery("#education_costs label").each(function(){
        jQuery(this).removeClass('js-form-required form-required');
      });

    }

    jQuery("#institution_have_sabc_code").on('change', function(){

      if(jQuery(this).val() == "Yes"){
        jQuery("#institution_sabc_code").show();
      }else{
        jQuery("#institution_sabc_code").hide();
      }
    });

    jQuery("#institution_type").on('change', function(){
        jQuery("#regulatory_information_out_province").hide();
        jQuery("#regulatory_information_out_province input[type='radio']").attr('disabled', true);
        jQuery("#regulatory_information_bc").hide();
        jQuery("#regulatory_information_bc input[type='radio']").attr('disabled', true);
        jQuery("#regulatory_information_us").hide();
        jQuery("#regulatory_information_international").hide();
        jQuery("#regulatory_information_international input[type='radio']").attr('disabled', true);

        jQuery("#regulatory_information_international_medical").hide();
        jQuery("#regulatory_information_international_medical input[type='radio']").attr('disabled', true);

        jQuery("#education_costs").hide();
        jQuery("#education_costs input").each(function(){
            jQuery(this).removeAttr('required');
        });
        jQuery("#education_costs label").each(function(){
          jQuery(this).removeClass('js-form-required form-required');
        });

      jQuery("#regulatory_information__institution_is_regulated").show();

        if(jQuery(this).val() == "B.C. Private"){ //BC
            jQuery("#regulatory_information_bc").show();
            jQuery("#regulatory_information_bc input[type='radio']").attr('disabled', false);

            jQuery("#education_costs").show();
            jQuery("#education_costs input").each(function(){
                jQuery(this).attr("required","required")
            });
            jQuery("#education_costs label").each(function(){
              jQuery(this).addClass('js-form-required form-required');
            });
            jQuery("#regulatory_information__institution_is_regulated").hide();

        }

        if(jQuery(this).val() == "Out of Province"){ //Out of Province
            jQuery("#regulatory_information_out_province").show();
            jQuery("#regulatory_information_out_province input[type='radio']").attr('disabled', false);
            jQuery("#regulatory_information__institution_is_regulated").hide();
        }

        if(jQuery(this).val() == "United States") { //US
          jQuery("#regulatory_information_us").show();
          jQuery("#regulatory_information__institution_is_regulated").hide();
          jQuery('#edit-regulatory-information-institution-approved-for-title-iv-code--wrapper').attr('required',true);

            var $titleIvCode = jQuery("#regulatory_information__title_iv_code");
            var $notEligible = jQuery("#regulatory_information__not_eligible");

            $titleIvCode.hide();
            $notEligible.hide();

            jQuery('input[name="regulatory_information__institution_approved_for_title_iv_code"]').change(function () {
              var isYes = jQuery(this).val() === "Yes";

              $titleIvCode.toggle(isYes);
              $notEligible.toggle(!isYes);
            });
        }

        if(jQuery(this).val() == "International"){ //International
            jQuery("#regulatory_information_international").show();
            jQuery("#regulatory_information_international input[type='radio']").attr('disabled', false);

            jQuery("#regulatory_information__institution_is_regulated").hide();
            jQuery("#regulatory_information_us").show();
        }

        if(jQuery(this).val() == "International Medical"){ //International Medical
            jQuery("#regulatory_information__institution_is_regulated").hide();
            jQuery("#regulatory_information_international_medical").show();
            jQuery("#regulatory_information_international_medical input[type='radio']").attr('disabled', false);

            jQuery("#regulatory_information_us").show();
        }

    });

    jQuery("#ptip_code").hide();
    jQuery("input[name='regulatory_information__bc_private_regulatory_body[Private Training Institutions Branch (PTIB)]']").on('change', function(){
        jQuery("#ptip_code").hide();
        if(jQuery("input[name='regulatory_information__bc_private_regulatory_body[Private Training Institutions Branch (PTIB)]']:checked").val() == 'Private Training Institutions Branch (PTIB)'){
            jQuery("#ptip_code").show();
        }
    });

    jQuery("#same_as_institution_address").on('change', function(e){
        if( jQuery(this).is(':checked') == true ){
            jQuery("#mailing_address").val(jQuery("#institution_address").val());
            jQuery("#mailing_city").val(jQuery("#institution_city").val());
            jQuery("#mailing_province").val(jQuery("#institution_province").val());
            jQuery("#mailing_country").val(jQuery("#institution_country").val());
            jQuery("#mailing_postal").val(jQuery("#institution_postal").val());
        }
    });

    //copy legal authority values to primary contact on user click on (same as Legal Authorized Authority)
    jQuery("#same_as_legal_authority").on('change', function(e){
        if( jQuery(this).is(':checked') == true ){
            jQuery("#institution_contact_legal_first_name2").val(jQuery("#institution_contact_legal_first_name").val());
            jQuery("#institution_contact_legal_last_name2").val(jQuery("#institution_contact_legal_last_name").val());
            jQuery("#institution_contact_legal_title2").val(jQuery("#institution_contact_legal_title").val());
            jQuery("#institution_contact_legal_email2").val(jQuery("#institution_contact_legal_email").val());
            jQuery("#institution_contact_legal_phone2").val(jQuery("#institution_contact_legal_phone").val());
            jQuery("#institution_contact_legal_bceid2").val(jQuery("#institution_contact_legal_bceid").val());
        }
    });


    jQuery("#program_not_eligible").hide();
    jQuery("#program_information_credential_extra_container").hide();
    jQuery("#program_information_credential").on('change', function(e){
        jQuery("#program_not_eligible").hide();
        jQuery("#program_information_credential_extra_container").hide();
        if( jQuery(this).val() == 'Other' ){
            jQuery("#program_information_credential_extra_container").show();
        }
        if( jQuery(this).val() == 'No Credential' ){
            jQuery("#program_not_eligible").show();
        }
    });

    jQuery("#program_not_eligible2").hide();
    jQuery("#program_information_total_weeks").on('blur', function(e){
        jQuery("#program_not_eligible2").hide();
        if( jQuery(this).val() != "" && parseInt(jQuery(this).val()) < 12 ){
            jQuery("#program_not_eligible2").show();
        }
    });

    jQuery("#program_not_eligible3").hide();
    jQuery("#program_information_admission_requirements_extra").on('blur', function(e){
        jQuery("#program_not_eligible3").hide();
        if( jQuery(this).val() != "" && parseInt(jQuery(this).val()) < 19 ){
            jQuery("#program_not_eligible3").show();
        }
    });

    jQuery("#program_information_program_code_container").hide();
    jQuery("input[name='program_information__previously_eligible']").on('change', function(e){
        jQuery("#program_information_program_code_container").hide();
        if( jQuery(this).is(':checked') == true && jQuery(this).val() == 'Yes' ){
            jQuery("#program_information_program_code_container").show();
        }
    });

  jQuery("#regulatory_information_title_iv_container").hide();
  jQuery("#institution_type").on('change', function(e){
    if(jQuery(this).val() == "United States"){ //US
      jQuery("#regulatory_information_title_iv_container").show();
      //var ele = jQuery("input[name='regulatory_information__us_institution_dept_education_approval']");
      //ele.attr('checked',false);
    }else{
      jQuery("#regulatory_information_title_iv_container").hide();
    }
  });
    /*
    jQuery("input[name='regulatory_information__us_institution_dept_education_approval']").on('change', function(e){
        jQuery("#regulatory_information_title_iv_container").hide();
        if( jQuery(this).is(':checked') == true && jQuery(this).val() == 'Yes' ){
            jQuery("#regulatory_information_title_iv_container").show();
        }
    });
*/
    jQuery("#institution_type").on('change', function(e){
        if(jQuery(this).val() == "United States" ||
            jQuery(this).val() == "International"||
            jQuery(this).val() == "International Medical"
        ){ //US
            //jQuery("#regulatory_information_title_iv_container").hide();
            //var ele = jQuery("input[name='regulatory_information__us_institution_dept_education_approval']");
            //ele.attr('checked',false);
        }
    });

    jQuery("#program_information_admission_requirements_extra_container").hide();
    jQuery("input[name='program_information__admission_requirements']").on('change', function(e){
        jQuery("#program_information_admission_requirements_extra_container").hide();
        if( jQuery(this).val() == "There is no minimum academic requirement" ){
            jQuery("#program_information_admission_requirements_extra_container").show();
        }
    });

    jQuery("#program_information_program_load_option1").hide();
    jQuery("#program_information_program_load_option2").hide();
    jQuery("input[name='program_information__course_load_type']").on('change', function(e){
        jQuery("#program_information_program_load_option1").hide();
        jQuery("#program_information_program_load_option2").hide();
        if( jQuery(this).val() == "Credits based" ){
            jQuery("#program_information_program_load_option1").show()


        }
        if( jQuery(this).val() == "Hours based" ){
            jQuery("#program_information_program_load_option2").show();
        }
    });

    jQuery("#program_not_eligible4").hide();
    jQuery("input[name='program_information__course_load_credits_met']").on('change', function(e){
        jQuery("#program_not_eligible4").hide();
        if( jQuery(this).val() == "No" ){
            jQuery("#program_not_eligible4").show();
        }
    });
    jQuery("#program_not_eligible5").hide();
    jQuery("input[name='program_information__course_load_hours_met']").on('change', function(e){
        jQuery("#program_not_eligible5").hide();
        if( jQuery(this).val() == "No" ){
            jQuery("#program_not_eligible5").show();
        }
    });

    jQuery("#program_information_type_practice_container").hide();
    jQuery("#program_information_type_practice_inputs_container").hide();
    jQuery("input[name='program_information__practice_education']").on('change', function(e){
        jQuery("#program_information_type_practice_container").hide();
        jQuery("#program_information_type_practice_inputs_container").hide();
        if( jQuery(this).val() == "Yes" ){
            jQuery("#program_information_type_practice_container").show();
            jQuery("#program_information_type_practice_inputs_container").show();
        }
    });

    jQuery("#program_information_type_practice1_container,"+
    "#program_information_type_practice_hour1_container").hide();
    //jQuery("#program_information_type_practice2_container,"+
    //"#program_information_type_practice_hour2_container").hide();
    jQuery("#program_information_type_practice3_container,"+
    "#program_information_type_practice_hour3_container").hide();
    jQuery("#program_information_type_practice4_container,"+
    "#program_information_type_practice_hour4_container").hide();
    jQuery("#program_information_type_practice5_container,"+
    "#program_information_type_practice_hour5_container").hide();

    jQuery("input[name^='program_information__required_education']").on('change', function(e){
        if( jQuery(this).val() == "Practicum" ){
            jQuery("#program_information_type_practice1_container,"+
            "#program_information_type_practice_hour1_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice1_container,"+
                "#program_information_type_practice_hour1_container").show();
            }
            var formattedPracticeHour1 = new Formatter(document.getElementById('program_information_type_practice_hour1'), {
                'pattern': '{{999}}',
            });
        }
        if( jQuery(this).val() == "Preceptership" ){
            jQuery("#program_information_type_practice3_container,"+
            "#program_information_type_practice_hour3_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice3_container,"+
                "#program_information_type_practice_hour3_container").show();
            }
            var formattedPracticeHour3 = new Formatter(document.getElementById('program_information_type_practice_hour3'), {
                'pattern': '{{999}}',
            });
        }
        if( jQuery(this).val() == "Other Work Integrated Learning (e.g. internship, paid work term, co-op)" ){
            jQuery("#program_information_type_practice4_container,"+
            "#program_information_type_practice_hour4_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice4_container,"+
                "#program_information_type_practice_hour4_container").show();
            }
            var formattedPracticeHour4 = new Formatter(document.getElementById('program_information_type_practice_hour4'), {
                'pattern': '{{999}}',
            });
        }

    });

    jQuery("#program_information_esl_percentage_container").hide();
    jQuery("input[name='program_information__esl']").on('change', function(e){
        jQuery("#program_information_esl_percentage_container").hide();
        if( jQuery(this).val() == "Yes" ){
            jQuery("#program_information_esl_percentage_container").show();
        }
    });

    jQuery("#program_not_eligible6").hide();
    jQuery("input[name='program_information__esl_20_percentage_or_less']").on('change', function(e){
        jQuery("#program_not_eligible6").hide();
        if( jQuery(this).val() == "No" ){
            jQuery("#program_not_eligible6").show();
        }
    });

    jQuery("#add_dates_year").hide();
    jQuery("#program_dates_container_year2").hide();
    jQuery("#program_dates_container_year3").hide();
    jQuery("#program_dates_container_year4").hide();
    jQuery("#program_dates_container_year5").hide();
    jQuery("#program_breaks_container_year2_breaks").hide();
    jQuery("#program_breaks_container_year3_breaks").hide();
    jQuery("#program_breaks_container_year4_breaks").hide();
    jQuery("#program_breaks_container_year5_breaks").hide();
    jQuery("#apply_all_program_dates_container").hide();
    jQuery("#program_information_number_years").on("change", function(){
        jQuery("#program_dates_container_year2").hide();
        jQuery("#program_dates_container_year3").hide();
        jQuery("#program_dates_container_year4").hide();
        jQuery("#program_dates_container_year5").hide();
        jQuery("#program_breaks_container_year2_breaks").hide();
        jQuery("#program_breaks_container_year3_breaks").hide();
        jQuery("#program_breaks_container_year4_breaks").hide();
        jQuery("#program_breaks_container_year5_breaks").hide();
        jQuery("#apply_all_program_dates_container").hide();
        jQuery("#add_dates_year").hide();

        if(jQuery(this).val() == "1 Year to <2 Years"){
            jQuery("#program_dates_container_year2").show();
            jQuery("#program_breaks_container_year2_breaks").show();
            jQuery("#apply_all_program_dates_container").show();
        }
        if(jQuery(this).val() == "2 Years to <3 Years"){
            jQuery("#program_dates_container_year2").show();
            jQuery("#program_dates_container_year3").show();
            jQuery("#program_breaks_container_year2_breaks").show();
            jQuery("#program_breaks_container_year3_breaks").show();
            jQuery("#apply_all_program_dates_container").show();
        }
        if(jQuery(this).val() == "3 Years to <4 Years"){
            jQuery("#program_dates_container_year2").show();
            jQuery("#program_dates_container_year3").show();
            jQuery("#program_dates_container_year4").show();
            jQuery("#program_breaks_container_year2_breaks").show();
            jQuery("#program_breaks_container_year3_breaks").show();
            jQuery("#program_breaks_container_year4_breaks").show();
            jQuery("#apply_all_program_dates_container").show();
        }
        if(jQuery(this).val() == "4 Years to <5 Years" || jQuery(this).val() == "5 Years or more"){
            jQuery("#program_dates_container_year2").show();
            jQuery("#program_dates_container_year3").show();
            jQuery("#program_dates_container_year4").show();
            jQuery("#program_dates_container_year5").show();
            jQuery("#program_breaks_container_year2_breaks").show();
            jQuery("#program_breaks_container_year3_breaks").show();
            jQuery("#program_breaks_container_year4_breaks").show();
            jQuery("#program_breaks_container_year5_breaks").show();
            jQuery("#apply_all_program_dates_container").show();
        }
        if(jQuery(this).val() == "5 Years or more"){
            jQuery("#add_dates_year").show();
        }
    });

    jQuery("#program_information_partner_institution_container").hide();
    jQuery("#program_information_student_payments_container").hide();
    jQuery("#program_information_designated_sabc_container").hide();
    jQuery("input[name='program_information__joint_program']").on('change', function(e){
        jQuery("#program_information_partner_institution_container").hide();
        jQuery("#program_information_student_payments_container").hide();
        jQuery("#program_information_designated_sabc_container").hide();
        if( jQuery(this).val() == "Yes" ){
            jQuery("#program_information_partner_institution_container").show();
            jQuery("#program_information_student_payments_container").show();
            jQuery("#program_information_designated_sabc_container").show();
        }
    });

    jQuery("#program_not_eligible7").hide();
    jQuery("input[name='program_information__partner_designated']").on('change', function(e){
        jQuery("#program_not_eligible7").hide();
        if( jQuery(this).val() == "No" ){
            jQuery("#program_not_eligible7").show();
        }
    });


  jQuery("#program_not_eligible9").hide();
  jQuery("input[name='regulatory_information__out_province_institution_provincial_approval']").on('change', function(e){
    jQuery("#program_not_eligible9").hide();
    if( jQuery(this).val() == "No" ){
      jQuery("#program_not_eligible9").show();
    }
  });

  jQuery("#program_not_eligible10").hide();
  jQuery("select[name='regulatory_information__institution_is_regulated']").on('change', function(e){
    jQuery("#program_not_eligible10").hide();
    if( jQuery(this).val() == "No" ){
      jQuery("#program_not_eligible10").show();
    }
  });


  jQuery("#apply_all_program_dates").on('change', function(){
        apply_to_all_dates();
    });
    jQuery("#add_dates_year").on('click', function(e){
        e.preventDefault();
        add_program_dates_and_breaks();
    });

    jQuery("#program_breaks_year1_start2_container").hide();
    jQuery("#program_breaks_year1_end2_container").hide();
    jQuery("#add_break_year1").on('change', function(){
        jQuery("#program_breaks_year1_start2_container").hide();
        jQuery("#program_breaks_year1_end2_container").hide();
        if( jQuery(this).is(':checked') == true ){
            jQuery("#program_breaks_year1_start2_container").show();
            jQuery("#program_breaks_year1_end2_container").show();
        }
    });
    /*
        // next/previous handlers
        jQuery("#institution_next").on('click', function(e){
            e.preventDefault();
            jQuery("#institution_information").hide();
            jQuery("#regulatory_information").show();
        });

        jQuery("#regulatory_previous").on('click', function(e){
            e.preventDefault();
            jQuery("#institution_information").show();
            jQuery("#regulatory_information").hide();
        });
        jQuery("#regulatory_next").on('click', function(e){
            e.preventDefault();
            jQuery("#institution_contact").show();
            jQuery("#regulatory_information").hide();
        });
    */

    jQuery("#add_breaks_year1").on('click', function(e){
        e.preventDefault();
        add_program_break(1); //year. represent an array position
    });
    jQuery("#add_breaks_year2").on('click', function(e){
        e.preventDefault();
        add_program_break(2); //year. represent an array position
    });
    jQuery("#add_breaks_year3").on('click', function(e){
        e.preventDefault();
        add_program_break(3); //year. represent an array position
    });
    jQuery("#add_breaks_year4").on('click', function(e){
        e.preventDefault();
        add_program_break(4); //year. represent an array position
    });
    jQuery("#add_breaks_year5").on('click', function(e){
        e.preventDefault();
        add_program_break(5); //year. represent an array position
    });

    jQuery("#apply_all_program_breaks").on('change', function(){
        apply_to_all_breaks();
    });
});


function apply_to_all_dates(){
    if( jQuery("#apply_all_program_dates").is(":checked") == true ){
        for(var row=1; row<20; row++){
            var year1_start = jQuery("input[name='program_dates__start_dates_year1']");
            var year1_end = jQuery("input[name='program_dates__end_dates_year1']");

            //if the year1 element exists in the DOM, then copy its value to other years, else break
            if (year1_start.length == 0) {
                break;
            }
            //loop thru other years
            for(var year=2; year<=number_of_years_shown; year++) {
                var start = jQuery("input[name='program_dates__start_dates_year" + year + "']");
                var end = jQuery("input[name='program_dates__end_dates_year" + year + "']");

                start.val(year1_start.val());
                end.val(year1_end.val());

            }
        }
    }
}

function apply_to_all_breaks(){
    if( jQuery("#apply_all_program_breaks").is(":checked") == true ){
        for(var row=1; row<20; row++){
            var year1_start = jQuery("input[name='program_breaks__start_year1_" + row + "']");
            var year1_end = jQuery("input[name='program_breaks__end_year1_" + row + "']");

            //if the year1 element exists in the DOM, then copy its value to other years, else break
            if (year1_start.length == 0) {
                break;
            }
            //loop thru other years
            for(var year=2; year<=number_of_years_shown; year++) {
                var start = jQuery("input[name='program_breaks__start_year" + year + "_" + row + "']");
                var end = jQuery("input[name='program_breaks__end_year" + year + "_" + row + "']");

                //if the target year element does not exist, then create a new field first
                if( start.length == 0 ){
                    add_program_break(year);
                }
            }
        }
        for(var row=1; row<20; row++){
            var year1_start = jQuery("input[name='program_breaks__start_year1_" + row + "']");
            var year1_end = jQuery("input[name='program_breaks__end_year1_" + row + "']");

            //if the year1 element exists in the DOM, then copy its value to other years, else break
            if (year1_start.length == 0) {
                break;
            }
            //loop thru other years
            for(var year=2; year<=number_of_years_shown; year++) {
                var start = jQuery("input[name='program_breaks__start_year" + year + "_" + row + "']");
                var end = jQuery("input[name='program_breaks__end_year" + year + "_" + row + "']");

                start.val(year1_start.val());
                end.val(year1_end.val());
            }
        }
    }
}

var number_of_years_shown = 5;
function add_program_dates_and_breaks() {
  add_program_date(number_of_years_shown);
  add_program_breaks(number_of_years_shown);
  number_of_years_shown++;
  convert_program_fields_to_date();
  convert_break_fields_to_date();
}

function add_program_breaks(num_years_currently_shown) {
  var num_years_plus_one = num_years_currently_shown + 1;
  var last_row = "program_breaks_container_year" + num_years_currently_shown + "_breaks";
  var new_element = '\
<div class="row" id="program_breaks_container_year' + num_years_plus_one + '_breaks">\
  <div id="edit-breaks' + num_years_plus_one + '" class="js-form-item form-item js-form-type-item form-type-item form-no-label"> \
    <div class="col-md-12" id="program_breaks_container_year' + num_years_plus_one + '_1"><br><strong>Year ' + num_years_plus_one + ': </strong>\
      <div id="edit-year' + num_years_plus_one + '-1" class="js-form-item form-item js-form-type-item form-type-item form-no-label"> \
        <div class="col" id="program_breaks_start_container_year' + num_years_plus_one + '_1">\
          <div class="js-form-item form-item js-form-type-textfield form-type-textfield form-item-program-breaks__start-year' + num_years_plus_one + '-1"> \
            <label for="edit-program-breaks-start-year' + num_years_plus_one + '-1">Start date:</label> \
            <input id="program_breaks_start_year' + num_years_plus_one + '_1" placeholder="yyyy-mm-dd" type="text" \
            name="program_breaks__start_year' + num_years_plus_one + '_1" value="" size="60" maxlength="128" class="form-text form-control">\
        </div> \
      </div>\
      <div class="col" id="program_breaks_end_container_year' + num_years_plus_one + '_1">\
        <div class="js-form-item form-item js-form-type-textfield form-type-textfield">\
          <label for="edit-program-breaks-end-year' + num_years_plus_one + '-1">End date:</label> \
          <input id="program_breaks_end_year' + num_years_plus_one + '_1" placeholder="yyyy-mm-dd" type="text" name="program_breaks__end_year' + num_years_plus_one + '_1" value="" size="60" maxlength="128" class="form-text form-control">\
        </div>\
      </div>\
      <div class="col"></div>\
    </div>\
  </div>\
  <div class="col-md-12">\
    <div id="edit-button' + num_years_plus_one + '" class="js-form-item form-item js-form-type-item form-type-item form-no-label">\
      <input id="add_breaks_year' + num_years_plus_one + '" class="btn btn-primary btn-small button" type="submit" name="op" value="Add another break for year ' + num_years_plus_one + '">\
    </div>\
  </div><br>\
  </div>\
</div>';
  jQuery("#" + last_row).after(new_element);
  jQuery("#add_breaks_year" + num_years_plus_one).on('click', function(e){
    e.preventDefault();
    add_program_break(num_years_plus_one); //year. represent an array position
  });
}

function add_program_date(num_years_currently_shown){
  var num_years_plus_one = num_years_currently_shown + 1;
  var last_row = "program_dates_container_year" + num_years_currently_shown;
  var new_element = '\
<div class="row" id="program_dates_container_year' + num_years_plus_one + '">\
    <div class="col-md-12">\
        <strong>Year ' + num_years_plus_one + ': </strong>\
    </div>\
    <div class="js-form-item form-item js-form-type-item form-type-item js-form-item-year5 form-item-year5 form-no-label">\
        <div class="col-md-4 ml-3">\
            <div class="control-group plain form-type-textfield form-item-start-dates-year' + num_years_plus_one + ' form-item">\
                <label for="edit-start-dates-year' + num_years_plus_one + '" class="control-label">Start date: </label>\
                <div class="controls">\
                    <input id="program_dates_start_year' + num_years_plus_one + '" placeholder="yyyy-mm-dd" type="text" name="program_dates__start_dates_year' + num_years_plus_one + '" value="" size="60" maxlength="128" class="form-text"/>\
                </div>\
            </div>\
        </div>\
        <div class="col-md-4">\
            <div class="control-group plain form-type-textfield form-item-end-dates-year' + num_years_plus_one + ' form-item">\
                <label for="edit-end-dates-year' + num_years_plus_one + '" class="control-label">End date: </label>\
                <div class="controls">\
                    <input id="program_dates_end_year' + num_years_plus_one + '" placeholder="yyyy-mm-dd" type="text" name="program_dates__end_dates_year' + num_years_plus_one + '" value="" size="60" maxlength="128" class="form-text"/>\
                </div>\
            </div>\
        </div>\
        <div class="col"></div>\
    </div>\
    </div>\
</div>';
    jQuery("#" + last_row).after(new_element);
}

//array representing the years. [# breaks 1st year, # breaks 2nd year, # breaks 3rd year, # breaks 4th year, # breaks 5th year]
var program_year_breaks = [1,1,1,1,1,1,1,1,1,1];
function add_program_break(year){
    var last_row = "program_breaks_container_year" + year + '_' + program_year_breaks[parseInt(year)-1];
    program_year_breaks[parseInt(year)-1] += 1;
    var year_name = "year" + parseInt(year) + "_" + program_year_breaks[parseInt(year)-1];
    var new_element = '\
<div class="col-md-12" style="width: 100%;" id="program_breaks_container_' + year_name + '">\
    <div class="control-group plain form-type-item form-item">\
        <div class="col-md-4" id="program_breaks_' + year_name + '_start_container">\
            <div class="control-group plain form-type-textfield form-item-start-breaks-' + year_name + ' form-item">\
                <label for="edit-start-breaks-' + year_name + '" class="control-label">Start date: </label>\
                <div class="controls">\
                    <input id="program_breaks_start_' + year_name + '" placeholder="yyyy-mm-dd" type="text" name="program_breaks__start_' + year_name + '" value="" size="60" maxlength="128" class="form-text">\
                </div>\
            </div>\
        </div>\
        <div class="col-md-4" id="program_breaks_end_container_' + year_name +'">\
            <div class="control-group plain form-type-textfield form-item-end-breaks-' + year_name + ' form-item">\
                <label for="edit-end-breaks-' + year_name + '" class="control-label">End date: </label>\
                <div class="controls">\
                    <input id="program_breaks_end_' + year_name + '" placeholder="yyyy-mm-dd" type="text" name="program_breaks__end_' + year_name + '" value="" size="60" maxlength="128" class="form-text">\
                </div>\
            </div>\
        </div>\
        <div class="col-md-4"></div>\
    </div>\
</div>';
    jQuery("#" + last_row).after(new_element);
    convert_break_fields_to_date();
}

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = 'https://www.google.com/recaptcha/api.js';
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'recaptcha-script'));

function convert_operation_field_to_date(){
    document.getElementById('institution_information__operation_since').addEventListener('input', function(e) {
        this.type = 'text';
        var input = this.value;
        if (/\D\/$/.test(input)) input = input.substr(0, input.length - 2);
        var values = input.split('/').map(function(v) {
            return v.replace(/\D/g, '')
        });
        if (values[0]) values[0] = checkValue(values[0], 12);
        if (values[1]) values[1] = checkValue(values[1], 2030);
        var output = values.map(function(v, i) {
            return v.length == 2 && i < 1 ? v + '/' : v;
        });
        this.value = output.join('').substr(0, 9);
    });
}

function convert_program_fields_to_date(){
    for(var year=1; year<=number_of_years_shown; year++) {
        if( jQuery("#program_dates_start_year" + year).length > 0 ) {

            document.getElementById('program_dates_start_year' + year).addEventListener('input', function(e) {
                this.type = 'text';
                var input = this.value;
                if (/\D\/$/.test(input)) input = input.substr(0, input.length - 4);
                var values = input.split('-').map(function(v) {
                    return v.replace(/\D/g, '')
                });
                //alert(values);
                if (values[0]) values[0] = checkValue(values[0], 2030);
                if (values[1]) values[1] = checkValue(values[1], 12);
                if (values[2]) values[2] = checkValue(values[2], 31);
                var output = values.map(function(v, i) {
                    if(i < 1)
                        return v.length == 4 && i < 1 ? v + '-' : v;
                    return v.length == 2 && i < 2 ? v + '-' : v;

                });
                this.value = output.join('').substr(0, 12);
            });
            document.getElementById('program_dates_end_year' + year).addEventListener('input', function(e) {
                this.type = 'text';
                var input = this.value;
                if (/\D\/$/.test(input)) input = input.substr(0, input.length - 4);
                var values = input.split('-').map(function(v) {
                    return v.replace(/\D/g, '')
                });
                //alert(values);
                if (values[0]) values[0] = checkValue(values[0], 2030);
                if (values[1]) values[1] = checkValue(values[1], 12);
                if (values[2]) values[2] = checkValue(values[2], 31);
                var output = values.map(function(v, i) {
                    if(i < 1)
                        return v.length == 4 && i < 1 ? v + '-' : v;
                    return v.length == 2 && i < 2 ? v + '-' : v;

                });
                this.value = output.join('').substr(0, 12);
            });
        }
    }
}
function convert_break_fields_to_date(){
    for(var year=1; year<=number_of_years_shown; year++) {
        for(var j=1; j<20; j++) {
            if( jQuery("#program_breaks_start_year" + year + '_' + j).length > 0 ) {

                document.getElementById('program_breaks_start_year' + year + '_' + j).addEventListener('input', function(e) {
                    this.type = 'text';
                    var input = this.value;
                    if (/\D\/$/.test(input)) input = input.substr(0, input.length - 4);
                    var values = input.split('-').map(function(v) {
                        return v.replace(/\D/g, '')
                    });
                    //alert(values);
                    if (values[0]) values[0] = checkValue(values[0], 2030);
                    if (values[1]) values[1] = checkValue(values[1], 12);
                    if (values[2]) values[2] = checkValue(values[2], 31);
                    var output = values.map(function(v, i) {
                        if(i < 1)
                            return v.length == 4 && i < 1 ? v + '-' : v;
                        return v.length == 2 && i < 2 ? v + '-' : v;

                    });
                    this.value = output.join('').substr(0, 12);
                });
                document.getElementById('program_breaks_end_year' + year + '_' + j).addEventListener('input', function(e) {
                    this.type = 'text';
                    var input = this.value;
                    if (/\D\/$/.test(input)) input = input.substr(0, input.length - 4);
                    var values = input.split('-').map(function(v) {
                        return v.replace(/\D/g, '')
                    });
                    //alert(values);
                    if (values[0]) values[0] = checkValue(values[0], 2030);
                    if (values[1]) values[1] = checkValue(values[1], 12);
                    if (values[2]) values[2] = checkValue(values[2], 31);
                    var output = values.map(function(v, i) {
                        if(i < 1)
                            return v.length == 4 && i < 1 ? v + '-' : v;
                        return v.length == 2 && i < 2 ? v + '-' : v;

                    });
                    this.value = output.join('').substr(0, 12);

                });
            }
        }
    }
}


function checkValue(str, max) {
    if (str.charAt(0) !== '0' || str == '00' || parseInt(str) > max) {
        var num = parseInt(str);
        if (isNaN(num) || num <= 0 || num > max) num = 1;
        str = num > parseInt(max.toString().charAt(0)) && num.toString().length == 1 ? '0' + num : num.toString();
    };

    return str;
};
jQuery(document).ready(function(){

  convert_program_fields_to_date();
    convert_break_fields_to_date();
    convert_operation_field_to_date();

    jQuery('[data-toggle="popover"]').popover();

    //on submit remove all values from invisible elements so they don't get submitted
    jQuery( ".sabc-designation-form" ).submit(function( event ) {
        jQuery(".sabc-designation-form input[type='text']").each(function() {
            if( jQuery(this).is(':visible') == false ){
                jQuery(this).val('');
                jQuery(this).prop('disabled', true);
            }
        });
        jQuery(".sabc-designation-form input[type='radio']").each(function() {
            if( jQuery(this).is(':visible') == false ){
                jQuery(this).val('');
                jQuery(this).prop('disabled', true);
            }
        });
        jQuery(".sabc-designation-form input[type='checkbox']").each(function() {
            if( jQuery(this).is(':visible') == false ){
                jQuery(this).val('');
                jQuery(this).prop('disabled', true);
            }
        });
    });

});






//if user attempted to resubmit and failed. The form will show up again and then show to hidden boxes based on selected option
jQuery(window).on('load', function() {


    document.getElementById('institution_information__operation_since').addEventListener('blur', function(e) {
      var date = e.target.value.split('/');

      //Date pattern MUST be month/day/year
      var oldDate  = new Date(date[0] + "/1/" + date[1]);
      var today = new Date();

      //Month is 0-11 in JavaScript
      //var today = today.getDate() + "/" + (today.getMonth() + 1) + "/" + today.getFullYear();

      const diffTime = Math.abs(today - oldDate);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      jQuery("#program_not_eligible8").hide();
      jQuery("#program_not_eligible8_1").hide();
      //show error if operation since is less than 10 years and is International Medical
      if( jQuery("#institution_type").val() == "International Medical"){
        if(diffDays < 3650){
          jQuery("#program_not_eligible8").show();
        }
      //else show error if operation since is less than 2 years
      }else{
        if(diffDays < 730){
          jQuery("#program_not_eligible8_1").show();
        }
      }

    });

    if(jQuery("input[name='regulatory_information__bc_private_regulatory_body[Private Training Institutions Branch (PTIB)]']:checked").val() == 'Private Training Institutions Branch (PTIB)'){
        jQuery("#ptip_code").show();
    }

    if( jQuery("#same_as_institution_address").is(':checked') == true ){
        jQuery("#mailing_address").val(jQuery("#institution_address").val());
        jQuery("#mailing_city").val(jQuery("#institution_city").val());
        jQuery("#mailing_province").val(jQuery("#institution_province").val());
        jQuery("#mailing_country").val(jQuery("#institution_country").val());
        jQuery("#mailing_postal").val(jQuery("#institution_postal").val());
    }

    if( jQuery("input[name='program_information__previously_eligible']").is(':checked') == true && jQuery("input[name='program_information__previously_eligible']").val() == 'Yes' ){
        jQuery("#program_information_program_code_container").show();
    }

    if( jQuery("#program_information_credential").val() == 'Other' ){
        jQuery("#program_information_credential_extra_container").show();
    }
    if( jQuery("#program_information_credential").val() == 'No Credential' ){
        jQuery("#program_not_eligible").show();
    }

    if( jQuery("input[name='program_information__admission_requirements']").val() == "There is no minimum academic requirement" ){
        jQuery("#program_information_admission_requirements_extra_container").show();
    }

    if( jQuery("#program_information_admission_requirements_extra").val() != "" && parseInt(jQuery("#program_information_admission_requirements_extra").val()) < 18 ){
        jQuery("#program_not_eligible3").show();
    }

    if( jQuery("input[name='program_information__practice_education']:checked").val() == "Yes" ){
        jQuery("#program_information_type_practice_container").show();
        jQuery("#program_information_type_practice_inputs_container").show();
    }

    if( jQuery("input[name='program_information__course_load_type']:checked").val() == "Credits based" ){
        jQuery("#program_information_program_load_option1").show();
    }
    if( jQuery("input[name='program_information__course_load_type']:checked").val() == "Hours based" ){
        jQuery("#program_information_program_load_option2").show();
    }

    if( jQuery("input[name='program_information__esl']:checked").val() == "Yes" ){
        jQuery("#program_information_esl_percentage_container").show();
    }


    if(jQuery("#program_information_number_years").val() == "1 Year to <2 Years"){
        jQuery("#program_dates_container_year2").show();
        jQuery("#program_breaks_container_year2_breaks").show();
        jQuery("#apply_all_program_dates_container").show();
    }
    if(jQuery("#program_information_number_years").val() == "2 Years to <3 Years"){
        jQuery("#program_dates_container_year2").show();
        jQuery("#program_dates_container_year3").show();
        jQuery("#program_breaks_container_year2_breaks").show();
        jQuery("#program_breaks_container_year3_breaks").show();
        jQuery("#apply_all_program_dates_container").show();
    }
    if(jQuery("#program_information_number_years").val() == "3 Years to <4 Years"){
        jQuery("#program_dates_container_year2").show();
        jQuery("#program_dates_container_year3").show();
        jQuery("#program_dates_container_year4").show();
        jQuery("#program_breaks_container_year2_breaks").show();
        jQuery("#program_breaks_container_year3_breaks").show();
        jQuery("#program_breaks_container_year4_breaks").show();
        jQuery("#apply_all_program_dates_container").show();
    }
    if(jQuery("#program_information_number_years").val() == "4 Years to <5 Years" || jQuery("#program_information_number_years").val() == "5 Years or more"){
        jQuery("#program_dates_container_year2").show();
        jQuery("#program_dates_container_year3").show();
        jQuery("#program_dates_container_year4").show();
        jQuery("#program_dates_container_year5").show();
        jQuery("#program_breaks_container_year2_breaks").show();
        jQuery("#program_breaks_container_year3_breaks").show();
        jQuery("#program_breaks_container_year4_breaks").show();
        jQuery("#program_breaks_container_year5_breaks").show();
        jQuery("#apply_all_program_dates_container").show();
    }
    if(jQuery("#program_information_number_years").val() == "5 Years or more"){
        jQuery("#add_dates_year").show();
    }

    jQuery("input[name^='program_information__required_education']").each(function(e) {
        if( jQuery(this).val() == "Practicum" ){
            jQuery("#program_information_type_practice1_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice1_container").show();
            }
        }
        if( jQuery(this).val() == "Clinical" ){
            jQuery("#program_information_type_practice2_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice2_container").show();
            }
        }
        if( jQuery(this).val() == "Preceptership" ){
            jQuery("#program_information_type_practice3_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice3_container").show();
            }
        }
        if( jQuery(this).val() == "Internship" ){
            jQuery("#program_information_type_practice4_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice4_container").show();
            }
        }
        if( jQuery(this).val() == "Paid work term" ){
            jQuery("#program_information_type_practice5_container").hide();
            if(jQuery(this).is(':checked') == true){
                jQuery("#program_information_type_practice5_container").show();
            }
        }
    });

    if(jQuery("#institution_type").val() == "B.C. Private"){ //BC
        jQuery("#regulatory_information_bc").show();
    }
    if(jQuery("#institution_type").val() == "Out of Province"){ //Out of Province
        jQuery("#regulatory_information_out_province").show();
    }
    if(jQuery("#institution_type").val() == "United States"){ //US
        jQuery("#regulatory_information_us").show();
    }
    if(jQuery("#institution_type").val() == "International"){ //International
        jQuery("#regulatory_information__institution_is_regulated").hide();
        jQuery("#regulatory_information_international").show();
    }
    if(jQuery("#institution_type").val() == "International Medical"){ //International Medical
        jQuery("#regulatory_information__institution_is_regulated").hide();
        jQuery("#regulatory_information_international_medical").show();
    }

    if( jQuery("input[name='program_information__joint_program']:checked").val() == "Yes" ){
        jQuery("#program_information_partner_institution_container").show();
        jQuery("#program_information_student_payments_container").show();
        jQuery("#program_information_designated_sabc_container").show();
    }

  /*
  if( jQuery("input[name='regulatory_information__international_institution_listed_with[U.S. Department of Education as approved for Title IV funding]']").is(':checked') == true ){
    jQuery("#regulatory_information__international_institution_us_dept_edu_iv_title").show();
  }
  */

  jQuery("#regulatory_information__international_institution_us_dept_edu_iv_title").hide();
  jQuery("input[name='regulatory_information__international_institution_listed_with[U.S. Department of Education has approved for Title IV funding]']").click(function(){
    //if( jQuery(this).is(':checked') == true && jQuery("#institution_type").val() == 'International'){
    if( jQuery(this).is(':checked') == true ){
      jQuery("#regulatory_information__international_institution_us_dept_edu_iv_title").show();
    }else {
      jQuery("#regulatory_information__international_institution_us_dept_edu_iv_title").hide();
    }
  });


  jQuery("#regulatory_information__international_medical_institution_us_dept_edu_iv_title").hide();
  jQuery("input[name='regulatory_information__international_medical_institution_listed_with[US]']").click(function(){
    //if( jQuery(this).is(':checked') == true && jQuery("#institution_type").val() == 'International'){
    if( jQuery(this).is(':checked') == true ){
      jQuery("#regulatory_information__international_medical_institution_us_dept_edu_iv_title").show();
    }else {
      jQuery("#regulatory_information__international_medical_institution_us_dept_edu_iv_title").hide();
    }
  });

  jQuery("#program_not_eligible11").hide();
  jQuery("input[name='regulatory_information__international_institution_listed_with[My institution is not listed with any of these options]']").click(function(){
    if( jQuery(this).is(':checked') == true){
      jQuery("#program_not_eligible11").show();
    }else{
      jQuery("#program_not_eligible11").hide();
    }
  });

  jQuery("#program_not_eligible12").hide();
  jQuery("input[name='regulatory_information__international_institution_approval']").click(function(){
    if(jQuery(this).val() == "No"){
        jQuery("#program_not_eligible12").show();
    }else{
      jQuery("#program_not_eligible12").hide();
    }
  });


  jQuery("#program_not_eligible13").hide();
  jQuery("input[name='regulatory_information__international_medical_institution_country_approval']").click(function(){
    if(jQuery(this).val() == "No"){
      jQuery("#program_not_eligible13").show();
    }else{
      jQuery("#program_not_eligible13").hide();
    }
  });

});

