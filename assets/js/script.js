  document.addEventListener("DOMContentLoaded", function() {
                // Update button click handler
                const updateButtons = document.querySelectorAll(".updateBtn");
                updateButtons.forEach(button => {
                    button.addEventListener("click", function(event) {
                        const row = event.target.closest("tr");
                        row.classList.remove("yellow", "orange", "red");
                        row.classList.add("green");
                        // Hide the row after marking as updated
                        row.style.display = "none";
                        // Prevent the default behavior of the button
                        event.preventDefault();
                    });
                });

                // Export functionality
                function exportCSV(color) {
                    const rows = color === "all"
                        ? document.querySelectorAll("#lastUpdated-Table tbody tr")
                        : document.querySelectorAll("#lastUpdated-Table tbody tr." + color);
                    let csvContent = "Title;Last Updated;Word Count;Changed Words";
                        csvContent += "\r\n"; 
                    rows.forEach(row => {
                        const cells = row.querySelectorAll("td");
                        if (cells.length > 0) {
                   
                            var title_line = cells[0].querySelectorAll(".title_line");
                            console.log( cells[6] );
                            csvContent += `${title_line[0].innerText};${cells[3].innerText};${cells[6].dataset.value};${cells[6].dataset.change}`;
                            csvContent += "\r\n"; 
                        }
                    });
                    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.setAttribute("href", url);
                    link.setAttribute("download", color === "all" ? "all_posts.csv" : color + "_posts.csv");
                    link.click();
                }
                window.exportCSV = exportCSV;

                // Filter by color functionality
                const filterColor = document.getElementById("filter-Color");
                
                if( filterColor )
                filterColor.addEventListener("change", function() {
                    const color = this.value;
                    const rows = document.querySelectorAll("#lastUpdated-Table tbody tr");
                    rows.forEach(row => {
                        if (color === "all" || row.classList.contains(color)) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });

                    // Display the table headers when filtering
                    document.querySelector("#lastUpdated-Table thead").style.display = "table-header-group";
                });
               
           
            });

 
jQuery(document).ready(function($){
    // Filter by title
    $('body').on("keyup", "#title_search", function(){
        var current_value = $(this).val().toLowerCase();
        $("#lastUpdated-Table tbody tr").each(function(){
            var this_val = $('.title_cell', this).html().toLowerCase();
            console.log( this_val );
            console.log(  this_val.includes( current_value ) );
            if( this_val.includes( current_value ) ){
                $(this).show();
            }else{
                $(this).hide();
            }
        })
    })

    // Change default options for ALL charts
    Chart.defaults.set('plugins.datalabels', {
        color: '#ffffff'
    });
    // chart initiation
    const ctx = document.getElementById("myChart");

    if( ctx )
        new Chart(ctx, {
            type: "pie",
            data: {
            labels: [ "Green", "Yellow", "Orange", "Red"],
            datasets: [{
                label: "",
                data: $.parseJSON( $('#pie_values').val() ),
                backgroundColor: [
                    'rgb(0, 205, 86)',
                    'rgb(255, 205, 86)',
                    'rgb(255, 100, 0)',
                    'rgb(255, 99, 132)',  
                  ],
                borderWidth: 1
            }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: false,
                  },
                  title: {
                    display: false,
                    text: 'Chart.js Pie Chart'
                  },
                  datalabels: {
                    formatter: (value) => {
           
                        return value + '%';
                      },
                  },
                }
            },
    });

    // tabs functionality
    $('body').on("click", ".single_nav_tab", function(){
        $('.single_nav_tab.active').removeClass('active');
        $(this).addClass('active');
        var id = $(this).attr('data-id');
        $('.single_tab').hide();
        $('.tab_'+id).show();
    })


    // ajax exclude
    $('body').on( 'click', '.exclude_post, .include_post', function( e ){
        var parent = $(this).parents('tr');

     
        if( $('.action_button', parent).hasClass('exclude_post') ){
            var action_type = 'exclude';
        }
        if( $('.action_button', parent).hasClass('include_post') ){
            var action_type = 'include';
        }
      
		// verify email
		var data = {
			id  : $(this).attr('data-id'),
			action_type  : action_type,
			security  : cump_local_data.nonce,
			action : 'exclude_post'
		}
		jQuery.ajax({url: cump_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						console.log( msg );
						$('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
						console.log( obj.success );
						if( obj.result == 'success' ){
                            $('.action_button.exclude_post', parent ).addClass('include_post');
                            $('.action_button.include_post', parent ).removeClass('exclude_post');
                            if( action_type == 'exclude' ){
                                $('.tab_excluded table tbody').append( parent );
                            }
                            if( action_type == 'include' ){
                                $('.tab_included table tbody').append( parent );
                            }
                            
                            //parent.replaceWith('');

						}else{
						 
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});	
	})

   // cancell note edition
    $('body').on( 'click', '#cancel_note_action', function( e ){
        $('.notes_edit_overlay').fadeOut();
    })
    var post_we_edit;
     // Notes edition
    $('body').on( 'click', '.view_note', function( e ){
        e.preventDefault();
        var parent = $(this).parents('tr');
        post_we_edit = $(this).attr('data-id');
		// verify email
		var data = {
			id  : $(this).attr('data-id'),
			security  : cump_local_data.nonce,
			action : 'view_note'
		}
		jQuery.ajax({url: cump_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						console.log( msg );
						$('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
						console.log( obj.success );
						if( obj.result == 'success' ){
                            $('.notes_edit_overlay').fadeIn();
                            $('.notes_title').html( obj.title );
                            $('#notes_body').val( obj.content );
                            //parent.replaceWith('');

						}else{
						 
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});	
	})
     // Notes edition
    $('body').on( 'click', '#save_note_action', function( e ){
     
		// verify email
		var data = {
			id  : post_we_edit,
			content  : $('#notes_body').val(),
			security  : cump_local_data.nonce,
			action : 'save_note'
		}
    
		jQuery.ajax({url: cump_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						console.log( msg );
						$('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
						console.log( obj.success );
						if( obj.result == 'success' ){
                            $('.notes_edit_overlay').fadeOut();
                          
                            //parent.replaceWith('');

						}else{
						 
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});	
	})
     // Notes editor save
    $('body').on( 'click', '#editor_save_notes', function( e ){
     
		// verify email
		var data = {
			id  : $('#post_ID').val(),
			content  : $('#_cutp_notes').val(),
			security  : cump_local_data.nonce,
			action : 'save_note'
		}
    
		jQuery.ajax({url: cump_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						console.log( msg );
						$('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
						console.log( obj.success );
						if( obj.result == 'success' ){
 
						}else{
						 
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});	
	})

    

    

})
// excel export
function export_clone( color, fn, dl ){
    if( color === "all" ){
        var selector = "#lastUpdated-Table tbody tr";
    }else{
        var selector = "#lastUpdated-Table tbody tr." + color;
    }
    
    jQuery('#excel_export tbody').html('');
    jQuery('#excel_export thead').html( jQuery("#lastUpdated-Table thead").html() );
    console.log( jQuery("#lastUpdated-Table thead").html() );
    
    jQuery('#excel_export thead tr').append('<th>Changed Words</th>');
    jQuery('#excel_export thead tr th:nth-child(8)').replaceWith('');
    jQuery(selector).each(function(){
        var row = jQuery(this).clone();
        var row_link = jQuery('.visit_url', row).attr('href');
        var row_title = jQuery('.title_line', row).html();
    
        var new_title = '<a href="'+row_link+'">'+row_title+'</a>';
      
        jQuery('.title_cell', row).html( new_title );

        jQuery('.counter_cell', row).html( jQuery('.counter_cell', row).attr('data-value') );
        jQuery('.counter_cell', row).after('<td>'+jQuery('.counter_cell', row).attr('data-change')+'</td>');

        jQuery('#excel_export tbody').append( row );
    })
   
    jQuery('#excel_export tbody tr td:nth-child(2), #excel_export thead tr th:nth-child(2)').replaceWith('');
    jQuery('#excel_export tbody tr td:nth-child(2), #excel_export thead tr th:nth-child(2)').replaceWith('');
    jQuery('#excel_export tbody tr td:nth-child(3), #excel_export thead tr th:nth-child(3)').replaceWith('');
    jQuery('#excel_export tbody tr td:nth-child(3), #excel_export thead tr th:nth-child(3)').replaceWith('');
    //jQuery('#excel_export tbody tr td:nth-child(4), #excel_export thead tr th:nth-child(4)').replaceWith('');
    jQuery('#excel_export tbody tr td:nth-child(5)').replaceWith('');
    //jQuery('#excel_export tbody tr td:nth-child(5), #excel_export thead tr th:nth-child(5)').replaceWith('');
    //jQuery('#excel_export tbody tr td:nth-child(5), #excel_export thead tr th:nth-child(5)').replaceWith('');
 
    

    var elt = document.getElementById('excel_export');
    
    var wb = XLSX.utils.table_to_book(elt );
 
    return XLSX.writeFile(wb,  'export.xlsx' );
}
 