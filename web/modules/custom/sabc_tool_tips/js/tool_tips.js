
// JavaScript Document
(function ($) {

  Drupal.behaviors.tool_tips = {
    attach: function (context, settings) {
		//GET ALL DATA-TOGGLES WITH TOOLTIP AND PUT INTO AN OBJECT
		var v = new Object();

		v = {0:'full-Time', 1:'study_period'}

		//MAKE SURE OUR OBJECT IS NOT EMPTY
		if(!jQuery.isEmptyObject(v)){
			//MAKE REQUEST AND SEND OUR OBJECT
			$.ajax({
			  dataType: "html",
			  type: 'POST',
			  data: v,
			  url: '/ajax/tool-tips'
			}).done(function( data ) {

				//MAKE SURE WE HAVE DATA BACK
				if(data != false){

					//PARSE JSON RESPONSE
					var obj = jQuery.parseJSON(data);

					//LOCATE ELEMENTS BASED ON CLASSNAME AND INJECT VALUE INTO DATA-ORIGINAL-TITLE
					jQuery.each(obj, function(i, val) {
						$('a.'+i+'').popover({trigger:'hover', placement:'bottom', html:true}).attr('data-content', val).click(function(e) {e.preventDefault()});
					});
				}
			})
		}
	}
  };


})(jQuery);
