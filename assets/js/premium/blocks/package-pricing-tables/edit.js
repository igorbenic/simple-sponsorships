const { RangeControl, RadioControl, ServerSideRender, Panel, PanelBody, PanelRow, SelectControl, Spinner, Toolbar } = wp.components;
const { __ } = wp.i18n;
const { Fragment, Component } = wp.element;
const { InspectorControls } = wp.editor;
const apiFetch = wp.apiFetch;

export default class Edit extends Component {
    constructor( props ) {
        super( ...props );
        this.props = props;
        this.state = {
            packages: [],
            button: props.attributes.button,
            id: props.attributes.id,
        };
        this.get_packages = this.get_packages.bind(this);
    }

    componentDidMount() {
        this.get_packages();
    }

    get_packages() {
        var self = this;
        fetch( ss_blocks.ajax, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            },
            body: 'action=ss_get_packages&nonce=' + ss_blocks.nonce,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json();
        }).then(function (res) {
            if ( res.success ) {
                self.setState({ packages: res.data })
            }
        });
    }

    render() {
        let packages = [{ label: __( 'Show All' ), value: 0 }]
        const { attributes, setAttributes } = this.props;
        const { button, id } = this.state;
        const columns = attributes.col;

        if ( this.state.packages.length ) {
            packages = packages.concat(this.state.packages.map(( post ) => {
                    return { label: post.title, value: post.ID }
                }));
        } else {
            packages = [];
        }

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Display Options' ) }
                        initialOpen={ false }>
                        <SelectControl
                            label={ __( 'Choose Package(s) to display' ) }
                            value={ attributes.id }
                            options={ packages }
                            onChange={ ( value ) => {

                                setAttributes({ id: value });
                                this.setState( { id: value } );

                            }}
                        />

                        <RangeControl
                            label={ __( 'Columns' ) }
                            value={ columns }
                            onChange={ ( nextColumns ) => {
                                setAttributes( {
                                    col: nextColumns
                                } );
                            } }
                            min={ 1 }
                            max={ 5 }
                        />

                        <RadioControl
                            label={ __( 'Show Purchase Button?' ) }
                            selected={ button }
                            options={ [
                                { value: '0', label: __( 'No' ) },
                                { value: '1', label: __( 'Yes' ) }
                            ] }
                            onChange={ ( value ) => {
                                setAttributes( { button: value } );
                                this.setState( { button: value } );
                            }}
                        />
                    </PanelBody>
                </InspectorControls>

                <ServerSideRender
                    block="simple-sponsorships/package-pricing-tables"
                    attributes={ attributes }
                        />
            </Fragment>
        );
    }
}