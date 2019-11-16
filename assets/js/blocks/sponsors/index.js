const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import SponsorsEdit from './edit';

registerBlockType( 'simple-sponsorships/sponsors', {
    title:  __( 'Sponsors' ),
    description: __( 'Show all sponsors or for a specific content' ),
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
        },
        type: {
            type: 'string',
            default: ''
        },
        col: {
            type: 'number',
            default: 2
        },
        link_sponsor: {
            type: 'string',
            default: '1'
        },
        hide_title: {
            type: 'string',
            default: '0'
        }
    },
    edit: SponsorsEdit,
    save() {
        return null;
    }
});