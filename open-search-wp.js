jQuery(function() {

    var OpenSearchWPKeyUp = _.debounce( function () {

        // Clear any previous results
        jQuery( '#oswp_search_results' ).remove();
        jQuery( '#oswp_search_searching' ).remove();

        // Get the search Input
        var searchInput = jQuery( this );

        // If the input value is empty, exit search.
        if( ! searchInput.val() ) {
            jQuery( '#oswp_search_searching' ).remove();
            return;
        }

        // Append "Searching" text
        searchInput.after( '<p id="oswp_search_searching" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white;">Searching...</p>' );

        var oswp_search = {
            action: 'oswp_search',
            okpreps_search_term: searchInput.val()
        };

        // AJAX Request
        jQuery.post(ajaxurl, oswp_search, function (response) {

            // Parse the response form the server.
            var response = JSON.parse( response );

            // Format wrapper for the results output.
            // TODO: Customize display wrapper.
            var resultHTML = jQuery( '<ul id="oswp_search_results" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white; border-right: 10px solid white;"></ul>' );

            // Initialize marker for seperating post types.
            var last_type = '';

            if( 0 != jQuery( response.results ).length ) {

                jQuery.each(response.results, function (index, value) {

                    if (value.type != last_type) {

                        // Append header for new post type divider.
                        // TODO: Customize display format.
                        resultHTML.append('<li><strong>' + value.type + '</strong></li>');
                        last_type = value.type;
                    }

                    // Append result.
                    // TODO: Customize display format.
                    resultHTML.append('<li><a href="' + value.href + '">' + value.text + '</a></li>');
                });
            } else {

                // No results were found.
                // TODO: Customize empty results display.
                resultHTML = jQuery( '<p id="oswp_search_results" style="margin: 0; padding: 10px; white-space: nowrap; overflow: hidden; background-color: white;">No Results Found.</p>' );
            }

            // Clear any previous results
            jQuery( '#oswp_search_results' ).remove();
            jQuery( '#oswp_search_searching' ).remove();

            // Append Search Results Output.
            searchInput.after( resultHTML );

            // DEBUG: Log server response.
            console.log( response );
        });

    }, 500 ); // debounce keyup listender by 500 milliseconds.

    // Bind KeyUp Listender to an element ont the page.
    // TODO: Attach to appropriate search box.
    jQuery('[name="s"]').keyup( OpenSearchWPKeyUp );
});