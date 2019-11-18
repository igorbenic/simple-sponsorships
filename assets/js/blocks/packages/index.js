const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import Edit from './edit';

registerBlockType( 'simple-sponsorships/packages', {
    title:  __( 'Packages' ),
    description: __( 'Show all or a specific package' ),
    icon: 'awards',
    category: 'simple-sponsorships',
    attributes: {
        id: {
            type: 'string',
            default: '0'
        },
        button: {
            type: 'string',
            default: '0'
        },
        col: {
            type: 'number',
            default: 1
        }
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                // Shortcode tag can also be an array of shortcode aliases
                tag: 'ss_packages',
                attributes: {
                    id: {
                        type: 'string',
                        shortcode: ( { named: { id = '0' } } ) => {
                            return id;
                        },
                    },
                    button: {
                        type: 'string',
                        shortcode: ( { named: { button = '0' } } ) => {
                            return button;
                        },
                    },
                },
            },
        ]
    },
    edit: Edit,
    save() {
        return null;
    }
});