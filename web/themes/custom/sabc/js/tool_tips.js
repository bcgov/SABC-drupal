
// JavaScript Document
(function ($) {

  Drupal.behaviors.tool_tips = {
    attach: function (context, settings) {
		//GET ALL DATA-TOGGLES WITH TOOLTIP AND PUT INTO AN OBJECT
		var v = new Object();
		// JAN 7 2015 - CHECK FOR *BOTH* 'TOOLTIPS' *AND* 'POPOVERS'
		// THIS ALLOWS FOR A DIFFERENCE IN OUTPUT BETWEEN DEV/TEST/PROD
		// need to test in IE
		$('a[data-toggle="popover"], a[data-toggle="tooltip"]').each(function(i){

			//MAKE SURE WE HAVE A CLASS DEFINED ON ELEMENT
			if($(this).attr('class') != undefined){
				v[i] = $(this).attr('class');
			}
		});

		//MAKE SURE OUR OBJECT IS NOT EMPTY
		if(!jQuery.isEmptyObject(v)){
			//MAKE REQUEST AND SEND OUR OBJECT
			$.ajax({
			  dataType: "html",
			  type: 'POST',
			  data: v,
			  url: '/ajax/tool-tips'
			}).done(function( data ) {
				console.log(data);

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
