const { RangeControl, RadioControl, ServerSideRender, FormTokenField, PanelBody, SelectControl, Spinner } = wp.components;
const { __ } = wp.i18n;
const { Fragment, Component } = wp.element;
const { InspectorControls } = wp.editor;
const apiFetch = wp.apiFetch;

export default class Edit extends Component {
    constructor( props ) {
        super( ...props );
        this.props = props;
        this.state = {
            suggestions: null,
            packages: [],
            button: props.attributes.button,
            id: props.attributes.id,
            ids: props.attributes.packages.split(','),
            selected: []
        };
        this.get_packages = this.get_packages.bind(this);
        this.getSuggetions = this.getSuggetions.bind(this);
        this.changeTokens = this.changeTokens.bind(this);
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
                const packages = res.data;
                const selected = packages.map(( post ) => {
                    if ( self.state.ids.indexOf( post.ID ) >= 0 ) {
                        return post.title;
                    }
                    return false;
                }).filter(Boolean);
                self.setState({ packages, selected });
            }
        });
    }

    changeTokens( tokens ) {
        let ids = [];
        let selected = [];
        tokens.forEach( ( title ) => {
            for( var p = 0; p < this.state.packages.length; p++ ) {
                var post = this.state.packages[ p ];
                if ( post.title === title ) {
                    ids.push( post.ID );
                    selected.push( title );
                    break;
                }
            }
        } );
        const packages = ids.join(',');

        this.setState({ ids, selected });
        this.props.setAttributes( { packages } );
    }

    getSuggetions() {
        if ( null === this.state.suggestions ) {
            if ( this.state.packages.length === 0 ) {
                this.get_packages();
            }
            if ( this.state.packages.length > 0 ) {
                let suggestions = [];
                suggestions = this.state.packages.map( ( post ) => {
                    return post.title;
                });
                this.setState( { suggestions } );
            }
        }
    }

    render() {
        let packages = [{ label: __( 'Show All' ), value: 0 }]
        const { attributes, setAttributes } = this.props;
        const { button, id, suggestions, selected } = this.state;
        let { ids } = this.state;
        const columns = attributes.col;
        
        if ( this.state.packages.length ) {
            packages = packages.concat(this.state.packages.map(( post ) => {
                    return { label: post.title, value: post.ID }
                })); 
        } else {
            packages = [];
        }
        
        if ( ids.length ) {
            ids = ids.filter((el) => !! el );
        }

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Display Options' ) }
                        initialOpen={ false }>
                        <FormTokenField 
                            label={ __( 'Choose Package(s) to display. Leave Empty for all.' ) }
                            value={ selected }
                            onChange={ ( tokens ) => {
                                this.changeTokens( tokens );
                                console.log('Tokens Changed');
                                console.log(tokens); 
                            } }
                            onInputChange={ (change) => {
                                this.getSuggetions();
                            } }
                            suggestions={ suggestions }
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