const { RadioControl, ServerSideRender, PanelBody, RangeControl, SelectControl, Toolbar } = wp.components;
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
            heading: props.attributes.heading || 'h2'
        };
        this.get_packages = this.get_packages.bind(this);
        this.changeHeading = this.changeHeading.bind(this);
    }

    componentDidMount() {
        this.get_packages();
    }

    createLevelControl( targetLevel, selectedLevel, onChange ) {
        return {
            icon: 'heading',
            // translators: %s: heading level e.g: "1", "2", "3"
            title: sprintf( __( 'Heading %d' ), targetLevel ),
            isActive: targetLevel === selectedLevel,
            onClick: () => onChange( targetLevel ),
            subscript: String( targetLevel ),
        };
    }

    changeHeading( value ) {
        const { setAttributes } = this.props;
        setAttributes({ heading: 'h' + value });
        this.setState( { heading: 'h' + value } );
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
        let packages = [{ label: __( 'Select a Package' ), value: 0 }]
        const { attributes, setAttributes } = this.props;
        const { button, id } = this.state;
        let columns = attributes.col || 1;

        if ( this.state.packages.length ) {
            packages = packages.concat(this.state.packages.map(( post ) => {
                return { label: post.title, value: post.ID }
            }));
        } else {
            packages = [];
        }

        let selectedHeading = parseInt( this.state.heading.replace( 'h', '' ) );

        return (
        <Fragment>
            <InspectorControls>
            <PanelBody
            title={ __( 'Display Options' ) }
            initialOpen={ false }>
                    <SelectControl 
                        label={ __( 'Choose a Package' ) }
                        value={ attributes.id }
                        options={ packages }
                        onChange={ ( value ) => {
                            
                            setAttributes({ id: value });
                            this.setState( { id: value } );
                            
                        }}
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
                        <p>{ __( 'Package Title' ) }</p>
                        <Toolbar controls={ [ 1, 2, 3, 4, 5, 6 ].map( ( index ) => this.createLevelControl( index, selectedHeading, this.changeHeading ) ) } />
                    
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
            </PanelBody>
            </InspectorControls>
           
            <ServerSideRender
                block="simple-sponsorships/packages"
                attributes={ attributes }
            />
        </Fragment>);
        }
}