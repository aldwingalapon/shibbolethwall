import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.caching = {

        module: 'caching',
        selectedServer: '',
        $serverSelector: null,
        $serverInstructions: [],
        $expirySelectors: [],
        $snippets: [],

        init: function () {
            let self                    = this,
                cachingMetabox          = $('#wphb-box-caching-enable'),
                cachingContent          = cachingMetabox.find('.box-content'),
                cachingContentSpinner   = cachingContent.find('.spinner'),
                cachingFooter           = cachingMetabox.find('.box-footer');

            if ( wphbCachingStrings )
                self.strings = wphbCachingStrings;

            this.$serverSelector = $( '#wphb-server-type' );
            this.selectedServer = this.$serverSelector.val();
            //this.$spinner = $('#wphb-box-caching-enable .spinner');

            self.$snippets.apache = $('#wphb-code-snippet-apache').find('pre').first();
            self.$snippets.nginx = $('#wphb-code-snippet-nginx').find('pre').first();

            let instructionsList = $( '.wphb-server-instructions' );
            instructionsList.each( function() {
                self.$serverInstructions[ $(this).data('server') ] = $(this);
            });

            let expirySelectors = $( '.wphb-expiry-select' );

            expirySelectors.each( function() {
                const type = $(this).data('type');
                if ( type ) {
                    $(this).change( function() {
                        //self.$spinner.css( 'visibility', 'visible' );
                        cachingContent.find('.wphb-content').hide();
                        cachingFooter.hide();
                        cachingContentSpinner.fadeIn();
                        $('.wphb-notice').hide();

                        // Expiration selector has changed
                        ( function( element ) {
                            const value = $( element ).val();
                            // Change the plugin settings
                            Fetcher.caching.setExpiration( type, value )
                                .then( () => {
                                    // And reload the code snippet
                                    self.reloadSnippets();
                                });
                            return false;
                        })( this );
                    });
                }

            });

            this.showServerInstructions( this.selectedServer );

            this.$serverSelector.change( function() {
                let value = $(this).val();
                self.hideCurrentInstructions();
                self.showServerInstructions( value );
                self.setServer(value);
                self.selectedServer = value;
            });

            $( '#toggle-apache-instructions').click( function( e ) {
                e.preventDefault();
                $('.apache-instructions').slideToggle();
            });

            $( '#toggle-litespeed-instructions').click( function( e ) {
                e.preventDefault();
                $('.litespeed-instructions').slideToggle();
            });


            return this;
        },

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },

        hideCurrentInstructions: function() {
            let selected = this.selectedServer;
            if ( this.$serverInstructions[ selected ] ) {
                this.$serverInstructions[ selected ].hide();
            }
        },

        showServerInstructions: function( server ) {
            if ( typeof this.$serverInstructions[ server ] !== 'undefined' ) {
                this.$serverInstructions[ server ].show();
            }

            if ( 'apache' === server || 'LiteSpeed' === server ) {
                $( '#enable-cache-wrap').show();
            }
            else {
                $( '#enable-cache-wrap').hide();
            }
        },

        reloadSnippets: function() {
            let self = this;
            let stop = false;
            for ( let i in self.$snippets ) {
                if ( self.$snippets.hasOwnProperty( i ) ) {
                    Fetcher.caching.reloadSnippets( i )
                        .then( ( response ) => {
                            if ( stop ) {
                                return;
                            }

                            self.$snippets[response.type].text( response.code );

                            // Make sure that we only do things when server displayed is the processed one
                            if ( response.type !== self.selectedServer ) {
                                return;
                            }

                            if ( 'apache' === response.type && response.updatedFile ) {
                                $( '#wphb-notice-code-snippet-htaccess-updated' ).show();
                                location.href = self.strings.recheckURL + '&caching-updated=true';
                            } else if ( 'apache' === response.type && self.strings.cacheEnabled && ! response.updatedFile ) {
                                $( '#wphb-notice-code-snippet-htaccess-error' ).show();
                                location.href = self.strings.htaccessErrorURL;
                            } else {
                                $( '#wphb-notice-code-snippet-updated' ).show();
                                location.href = self.strings.recheckURL + '&caching-updated=true';
                            }
                            //self.$spinner.css( 'visibility', 'hidden' );
                        });
                }
            }
        }
    };
}( jQuery ));