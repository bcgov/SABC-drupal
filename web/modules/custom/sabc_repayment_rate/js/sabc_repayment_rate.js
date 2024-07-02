	(function ($) {
 Drupal.behaviors.sabc_repayment_rate = {
    attach: function (context, settings) {

		/*
		* Alphabet Filter
		*/
		if ( typeof $.fn.dataTable == "function" &&
			 typeof $.fn.dataTableExt.fnVersionCheck == "function" &&
			$.fn.dataTableExt.fnVersionCheck('1.9.4') ){
				$.fn.dataTableExt.aoFeatures.push( {
						"fnInit": function( oDTSettings ) {

							var alphaFilter = new AlphabetFilter( oDTSettings );
							return alphaFilter.getContainer();
						},

						"cFeature": "A",
						"sFeature": "AlphaFilter"

				} );
		}
		else
		{
			alert( "Warning: PagingControl requires DataTables 1.9.4 or greater - www.datatables.net/download");
		}

		var AlphabetFilter = function ( oDTSettings ){
			var me = this;
			me.$FilterContainer = $( '<div class="alphabet-filter-widgets btn-group clearfix">'+
			'<a class="btn alphabet-filter mobile-one columns" id="filter_all"><span>View All</span></a>');//open container

			//loop through alphabet and build filter buttons
			var first = "a", last = "z";
			for(var i = first.charCodeAt(0); i <= last.charCodeAt(0); i++) {
				me.$FilterContainer.append('<a class="btn alphabet-filter mobile-one columns" id="filter_'+ String.fromCharCode(i) +'"><span>' + String.fromCharCode(i) + '</span></a>');
			}
			me.$FilterContainer.append('</div>');

			oDTSettings.aoDrawCallback.push( {
					"fn": function () {	},
					"sName": "AlphaFilter"
			} );

			return me;

		};


		/**
		* Get the container node of the column filter widgets.
		*
		* @method
		* @return {Node} The container node.
		*/
		AlphabetFilter.prototype.getContainer = function() {
			return this.$FilterContainer.get( 0 );
		}

		/*
		* Custom Pagination
		*/

		$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
		{
			return {
				"iStart":         oSettings._iDisplayStart,
				"iEnd":           oSettings.fnDisplayEnd(),
				"iLength":        oSettings._iDisplayLength,
				"iTotal":         oSettings.fnRecordsTotal(),
				"iFilteredTotal": oSettings.fnRecordsDisplay(),
				"iPage":          oSettings._iDisplayLength === -1 ?
					0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
				"iTotalPages":    oSettings._iDisplayLength === -1 ?
					0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
			};
		}

		/*Custom classes for datatable*/
		$.extend($.fn.dataTableExt.oStdClasses, {
			/* Full numbers paging buttons */
			"sPageButton": "btn paginate_button",
			"sPageButtonActive": "btn paginate_active active",
			"sPageButtonStaticDisabled": "btn paginate_button paginate_button_disabled disabled ",
			"sPageFirst": "first",
			"sPagePrevious": "previous",
			"sPageNext": "next",
			"sPageLast": "last",
			"sSortAsc": "tableSortDown",
			"sSortDesc": "tableSortUp",
			"sSortable": "tableSort",
			"sWrapper": "dataTables_wrapper clearfix"
		});

		//hide table until the results have loaded
		$('#repayment-rate tbody').hide();

		var oTable = $('#repayment-rate').dataTable({
			"fnInitComplete": function() {
				$('#repayment-rate.dataTable tbody').show();
				return true;
			},
			"sDom": '<"datatable-header"fA>rt<"datatable-footer"<"four columns"i><"eight mobile-four columns"p>>',
			"sPaginationType": "full_numbers",
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sEmptyTable": '<div class="no-results"><strong>No Results Found</strong></div>',
				"sZeroRecords": '<div class="no-results"><strong>No Results Found</strong></div>',
				"sSearch": 'Search for an institution <a href="#" data-toggle="popover" class="repayment_rate_search_tips">' +
				'Search Tips <i class="icon-supportrequest"></i></a>',
				"sProcessing": "<h3>Please wait while the repayment rates load...</h3>"
			},
			"processing": true,
			"ajax": "/repayment-rate-data-load",
			"columns": [
				{ "data": "Institution",
				"defaultContent": "" },
				{ "data": "City",
				"defaultContent": "" },
				{ "data": "Borrowers",
				"orderable": false,
				"defaultContent": "" },
				{ "data": "year1",
				"orderable": false,
				"defaultContent": "" },
				{"data": "year2",
				"orderable": false,
				"defaultContent": "",	},
				{"data": "year3",
				"orderable": false,
				"defaultContent": "",	},
			],
			"deferRender": true
		});

		$('.dataTables_filter input').after('<i class="icon-uniF470 clear-input"></i>');

		/*
		 * Repayment Rate Alphabet Event Handler
		 * Filters the results based on letter
		 */
		$('#repayment-rate_wrapper').on('click','.alphabet-filter', function() {

			oTable.fnFilter("", 0);//reset existing filter

			resetTable();

			if($(this).hasClass('active')) {
				$('a.active').removeClass('active');//remove any active classes from letters
				oTable.fnFilter("", 0);//reset filter if already active
			} else {
				if($(this).is('#filter_all')){
					//reset all reset if view all is clicked
					oTable.fnFilter("", 0);
					$('a.active').removeClass('active');
				} else {
					//filter results based on letter clicked
					$('a.active').removeClass('active');
					$(this).addClass('active');
					var alpha_character = $(this).find("span").html();
					oTable.fnFilter("^"+ alpha_character, 0, 1 );//datable regular expression
				}
			}
		} );

		//When pagination buttons are clicked scroll to top of the table
		$('.datatable-footer').on('click', '.btn.paginate_button', function(event) {
			event.preventDefault();
			$('#repayment-rate_wrapper').scrollView();
		});


		/*
		 * Repayment Rate Search Input Filter Handler
		 * Resets the alphabet filters on typing and adds clear button to input if not empty
		 */

		// Isn't it awesome when Microsoft makes their own version of Javascript?
		// http://msdn.microsoft.com/en-ca/library/ie/ms536956(v=vs.85).aspx
		$('.dataTables_filter input').on('input propertychange', function() {
			oTable.fnFilter("", 0);
			resetTable();
			$('.alphabet-filter-widgets .active').removeClass('active');
			if($(this).val() == ""){
				$(this).parent().removeClass('active');
			} else {
				if(!($(this).parent().hasClass('active'))){
					$(this).parent().addClass('active');
				}
			}
		});

		/*
		 * Repayment Rate Search ClearEvent Handler
		 * Reset the the input filters/results
		 */

		$('.dataTables_filter i.clear-input').on('click', function() {
			$('.dataTables_filter input').val(""); //reset search filter
			resetTable();// reset repayment rate
			oTable.fnFilter("");//reset alphabet filter
			$(this).parent().removeClass('active');//remove active class from buttons
		});


		//Remove the repayment rate details and reset the table
		function resetTable(){
			if($('.ajax-loading').is(':visible')) {
				$('.ajax-loading').hide();
				$('a.back-to-search').remove();//remove back to results link
				$('#repayment-rate.dataTable').toggle();
				$('.datatable-footer').toggle();
			}
		}

		if($('#slider').length == 0) {
			$( "<div id='slider'></div>" ).insertAfter('#repayment-calculator-form select.sliderBar').slider({
				value:10,
				min: 1,
				max: 10,
				step: 1,
				slide: function( event, ui ) {
					 $( "#repayment-calculator-form select.sliderBar" ).val( ui.value );
				}
			  }).each(function() {
	
				//
				// Add labels to slider whose values 
				// are specified by min, max and whose
				// step is set to 1
				//
			  
				// Get the options for this slider
				var opt = $(this).data().uiSlider.options;
				
				// Get the number of possible values
				var vals = opt.max - opt.min;
				
				// Space out values
				for (var i = 0; i <= vals; i++) {
				  
				  var el = $('<label>'+(i+1)+'</label><span class="label-num">'+(i+1)+'</span>').css('left',(i/vals*100)+'%');
				
				  $( "#slider" ).append(el);
				  
				}
			});
		}

		$('<span class="add-on"><strong>$</strong></span>').insertBefore('#loan-repayment-estimator .form-item-loan-amt input');
		$('<span class="add-on"><strong>%</strong></span>').insertAfter('#loan-repayment-estimator .form-item-prime-rate input');

		$('.form-item-loan-type:first-of-type label').addClass('active');
		$('.form-item-loan-type label').on('click', function() {
			$('.form-item-loan-type label').removeClass('active');
			$(this).addClass('active');
		});


		$('#repayment-calculator-form input[type="submit"]').on('click', function(e){
			e.preventDefault();
			$('#loan_summary').empty();

			var loan_amt = $('#edit-loan-amt').val();
			loan_amt = parseFloat(encodeURIComponent(loan_amt.replace(',','',loan_amt)));
			
			var loan_type = $('input[name="loan_type"]:checked').val();
			var prime_rate = parseFloat($('#edit-prime-rate').val());
			var loan_period = parseFloat($('#loan_period').val());
			var post_v = 
				{
					"loan_amt": loan_amt,	
					"loan_type": loan_type,
					"prime_rate": prime_rate,
					"loan_period": loan_period
				}
			$('#loan_summary').prepend('<span class="waiting">Loading ....</span>');
			$.ajax({
				dataType: "html",
				type: 'POST',
				data: post_v,
				url: '/ajax/repay'
			  }).done(function( data ) {
				  $('#loan_summary').append(data);
				  $('.waiting').hide();
			  })
		})
		
    }
};
})(jQuery);
