const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { Fragment } = wp.element;
const { InspectorControls } = wp.editor;
const { ServerSideRender, Panel, PanelBody, PanelRow } = wp.components;


registerBlockType( 'simple-sponsorships/sponsors', {
    title:  __( 'Sponsors' ),
    icon: 'awards',
    category: 'simple-sponsorships',
    attributes: {
        all: {
            type: 'string',
            default: '0'
        },
        content: {
            type: 'string',
            default: 'current'
        },
        logo: {
            type: 'string',
            default: '1'
        },
        text: {
            type: 'string',
            default: '1'
        },
        package: {
            type: 'string',
            default: '0'
        }
    },
    edit( props ) {
        const { attributes } = props;
        return <Fragment>
            <InspectorControls>
            <PanelBody
            title={ __( 'Display Options' ) }
            initialOpen={ true } >
                <PanelRow>
                My Panel Inputs and Labels
                </PanelRow>
            </PanelBody>

            </InspectorControls>
        <ServerSideRender
            block="simple-sponsorships/sponsors"
            attributes={ attributes }
        />
            </Fragment>;
    },
    save() {
        return null;
    }
});