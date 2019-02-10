const { RadioControl, ServerSideRender, Panel, PanelBody, PanelRow, SelectControl, Spinner } = wp.components;
const { __ } = wp.i18n;
const { Fragment, Component } = wp.element;
const { InspectorControls } = wp.editor;
const apiFetch = wp.apiFetch;

function get_current_content_id() {
    // Remove ? and then construct an array of objects.
    const params = window.location.search.replace('?', '').split('&').map( (param) => { 
        const args = param.split('='); 
        return { tag: args[0], value: args[1]}; 
    });

    let id = 'current';
    if ( params.length ) {
        for( var querystring in params ) {
            const object = params[ querystring ];
            if ( 'post' === object['tag'] ) {
                id = object['value'];
            }
        }
    }

    return id;
}

export default class Edit extends Component {
    constructor( props ) {
        super( ...props );
        this.props = props;
        console.log(props);
        this.state = { 
            displayOption: props.attributes.content !== 'all' && props.attributes.content !== 'current' ? 'other' : this.props.attributes.content,
            content: [],
            type: props.attributes.type && props.attributes.all !== '1' ? props.attributes.type : '',
            loading: false,
        }

        if ( props.attributes.all === '1' ) {
            this.state.displayOption = 'all';
        }

        this.get_content = this.get_content.bind(this);
    }

    componentDidMount() {
        if ( this.state.type && 'all' !== this.state.displayOption ) {
            this.get_content( this.state.type );
        }
    }

    get_content( type ) {
        let _type = type;
        if ( 'post' === type || 'page' === type ) {
            _type = type + 's';
        }

        this.setState({ loading: true });
        
        apiFetch( { path: '/wp/v2/' + _type } ).then( content => {
            this.setState( { content, type, loading: false } );
        } );
    }

    render() {
        let content_options = [{ label: __( 'Select a Content' ), value: 'current' }];
        let content_types   = [{ label: __( 'Select a Content Type' ), value: '' }];
        const { content, displayOption, type, loading } = this.state;
        const { attributes, setAttributes } = this.props;

        if ( content.length ) {
            content_options = content_options.concat(content.map(( post ) => {
                return { label: post.title.rendered, value: post.id }
            }));
        } else {
            content_options = [];
        }
        
        const types = Object.keys( ss_blocks.content_types ).map(( content_type ) => {
            return { label: ss_blocks.content_types[ content_type ], value: content_type }
        });
        content_types = content_types.concat( types );

        return (
        <Fragment>
            <InspectorControls>
            <PanelBody
            title={ __( 'Display Options' ) }
            initialOpen={ true } >
                <PanelRow>
                    <RadioControl
                        label={ __( 'Show' ) }
                        help={ __( 'What Sponsors to show' ) }
                        selected={ displayOption }
                        options={ [
                            { label: __( 'All Sponsors' ), value: 'all' },
                            { label: __( 'Sponsors that sponsored this Content' ), value: 'current' },
                            { label: __( 'Sponsors that sponsored other Content' ), value: 'other' }
                        ] }
                        onChange={ ( displayOption ) => { 
                            this.setState( { displayOption } );
                            if ( 'all' === displayOption ) {
                                setAttributes( { all: '1' } );
                            }
                            if ( 'current' === displayOption ) {

                                setAttributes( { all: '0', content: get_current_content_id() } );
                            }
                        } }
                    />
                    
                </PanelRow>
                { 'other' === displayOption &&
                    [<PanelRow>
                        <SelectControl 
                            label={ __( 'Content Type' ) }
                            value={ type }
                            options={ content_types }
                            onChange={ ( type ) => {
                                if ( type ) {
                                    this.get_content( type );
                                    setAttributes({ type });
                                } else {
                                    this.setState({ content: [], type: type });
                                    setAttributes( { type: type, all: '0', content: 'current' } );
                                }
                            }}
                        />
                    </PanelRow>,
                    <PanelRow>
                        <SelectControl 
                            label={ __( 'Content' ) }
                            value={ attributes.content }
                            options={ content_options }
                            onChange={ ( content ) => {
                                setAttributes( { all: '0', content: content } );
                            }}
                        />
                        { loading && <Spinner />}
                    </PanelRow>]
                }
            </PanelBody>

            </InspectorControls>
           
            <ServerSideRender
                block="simple-sponsorships/sponsors"
                attributes={ attributes }
            />
        </Fragment>);
        }
}