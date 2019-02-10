const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import SponsorsEdit from './sponsors/edit';

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
        },
        type: {
            type: 'string',
            default: ''
        }
    },
    edit: SponsorsEdit,
    save() {
        return null;
    }
});