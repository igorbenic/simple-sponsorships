const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
import Edit from './edit';

registerBlockType( 'simple-sponsorships/package-pricing-tables', {
    title:  __( 'Packages Pricing Tables' ),
    description: __( 'Show packages as pricing tables' ),
    icon: 'awards',
    category: 'simple-sponsorships',
    attributes: {
        packages: {
            type: 'string',
            default: ''
        },
        button: {
            type: 'string',
            default: '0'
        },
        col: {
            type: 'number',
            default: 2
        }
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                // Shortcode tag can also be an array of shortcode aliases
                tag: 'ss_package_pricing_tables',
                attributes: {
                    packages: {
                        type: 'string',
                        shortcode: ( { named: { packages = '' } } ) => {
                            return id;
                        },
                    },
                    button: {
                        type: 'string',
                            shortcode: ( { named: { button = '0' } } ) => {
                            return button;
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