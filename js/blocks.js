( function( blocks, element, components, blockEditor ) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType( 'encrypt-blogs/encrypted-content', {
        title: 'Encrypted Content',
        icon: 'lock',
        category: 'common',
        attributes: {
            content: {
                type: 'string',
                source: 'text',
            },
            startDate: {
                type: 'string',
            },
            endDate: {
                type: 'string',
            },
            displayMode: {
                type: 'string',
                default: 'message',
            },
        },
        edit: function( props ) {
            var blockProps = useBlockProps();
            
            return el( 'div', null, [
                // Sidebar Controls
                el( InspectorControls, {},
                    el( PanelBody, {
                        title: 'Display Settings',
                        initialOpen: true
                    },
                        el( TextControl, {
                            label: 'Start Date',
                            help: 'Format: YYYY-MM-DD HH:mm| MM-DD HH:mm | HH:mm',
                            value: props.attributes.startDate,
                            onChange: function( value ) {
                                props.setAttributes( { startDate: value } );
                            },
                        } ),
                        el( TextControl, {
                            label: 'End Date',
                            help: 'Format: YYYY-MM-DD HH:mm| MM-DD HH:mm | HH:mm',
                            value: props.attributes.endDate,
                            onChange: function( value ) {
                                props.setAttributes( { endDate: value } );
                            },
                        } ),
                        el( SelectControl, {
                            label: 'Display Mode',
                            value: props.attributes.displayMode,
                            options: [
                                { label: 'Hidden', value: 'hidden' },
                                { label: 'Show Encrypted', value: 'encrypted' },
                                { label: 'Show Message', value: 'message' },
                                { label: 'Redacted', value: 'redacted' },
                            ],
                            onChange: function( value ) {
                                props.setAttributes( { displayMode: value } );
                            },
                        } )
                    )
                ),
                // Main editor area control
                el( 'div', blockProps,
                    el( TextControl, {
                        label: 'Content',
                        value: props.attributes.content,
                        onChange: function( value ) {
                            props.setAttributes( { content: value } );
                        },
                    } )
                )
            ] );
        },
        save: function( props ) {
            return el( 'div', useBlockProps.save(),
                props.attributes.content
            );
        },
    } );
} )( window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor );
