jQuery(function() {

    var OpenSearchWPKeyUp = _.debounce( function () {

        // Clear any previous results
        jQuery( '#oswp_search_results' ).remove();
        jQuery( '#oswp_search_searching' ).remove();

        var searchInput = jQuery( this );

        searchInput.after( '<p id="oswp_search_searching" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white;">Searching...</p>' );

        var oswp_search = {
            action: 'oswp_search',
            //security: '<?php echo wp_create_nonce( 'ninja_forms_ajax_nonce' ); ?>',
            okpreps_search_term: searchInput.val()
        };

        if( ! searchInput.val() ) {

            jQuery( '#oswp_search_searching' ).remove();

            return;
        }

        jQuery.post(ajaxurl, oswp_search, function (response) {

            var response = JSON.parse( response );

            var resultHTML = jQuery( '<ul id="oswp_search_results" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white; border-right: 10px solid white;"></ul>' );

            var last_type = '';

            if( 0 != jQuery( response.results).length ) {

                jQuery.each(response.results, function (index, value) {

                    if (value.type != last_type) {
                        resultHTML.append('<li><strong>' + value.type + '</strong></li>');
                        last_type = value.type;
                    }

                    //resultHTML.append('<li>' + value.site + ' - <a href="' + value.href + '">' + value.text + '</a></li>');
                    resultHTML.append('<li><a href="' + value.href + '">' + value.text + '</a></li>');
                });
            } else {
                resultHTML = jQuery( '<p id="oswp_search_results" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white;">No Results Found.</p>' );
            }

            // Clear any previous results
            jQuery( '#oswp_search_results' ).remove();
            jQuery( '#oswp_search_searching' ).remove();

            searchInput.after( resultHTML );

            console.log( response );
        });

    }, 500 );

    jQuery('[name="s"]').keyup( OpenSearchWPKeyUp );
});