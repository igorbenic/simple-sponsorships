const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import Edit from './edit';

registerBlockType( 'simple-sponsorships/form-sponsor', {
    title:  __( 'Form - Sponsor' ),
    description: __( 'Show a form for sponsoring content' ),
    icon: 'awards',
    category: 'simple-sponsorships',
    attributes: {
        packages: {
            type: 'string',
            default: ''
        }
    },
    edit: Edit,
    save() {
        return null;
    }
});