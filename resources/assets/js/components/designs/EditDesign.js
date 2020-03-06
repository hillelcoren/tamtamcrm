import React from 'react'
import { Button, Modal, ModalHeader, ModalBody, ModalFooter, Input, FormGroup, Label, DropdownItem } from 'reactstrap'
import axios from 'axios'
import DesignDropdown from '../common/DesignDropdown'
import CKEditor from '@ckeditor/ckeditor5-react'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

class EditDesign extends React.Component {
    constructor ( props ) {
        super ( props )
        this.state = {
            modal: false,
            id: this.props.design.id,
            name: this.props.design.name,
            design: this.props.design.design,
            loading: false,
            changesMade: false,
            errors: [],
            obj_url: null
        }

        this.initialState = this.state
        this.toggle = this.toggle.bind ( this )
        this.handleClick = this.handleClick.bind ( this )
        this.hasErrorFor = this.hasErrorFor.bind ( this )
        this.renderErrorFor = this.renderErrorFor.bind ( this )
        this.getPreview = this.getPreview.bind ( this )
        this.switchDesign = this.switchDesign.bind ( this )
    }

    handleInput ( e ) {
        this.setState ( {
            [e.target.name]: e.target.value,
            changesMade: true
        } )
    }

    switchDesign ( design ) {
        console.log('design', design[0])
        this.setState ( {
            design: design[0].design,
            name: design[0].name
        } )
    }

    hasErrorFor ( field ) {
        return !!this.state.errors[ field ]
    }

    renderErrorFor ( field ) {
        if ( this.hasErrorFor ( field ) ) {
            return (
                <span className='invalid-feedback'>
                    <strong>{this.state.errors[ field ][ 0 ]}</strong>
                </span>
            )
        }
    }

    handleClick () {
        axios.put ( `/api/designs/${this.state.id}`, {
                name: this.state.name,
                design: this.state.design
            } )
            .then ( ( response ) => {
                this.toggle ()
                const index = this.props.designs.findIndex ( design => design.id === this.state.id )
                this.props.designs[ index ] = response.data
                this.props.action ( this.props.designs )
            } )
            .catch ( ( error ) => {
                this.setState ( {
                    errors: error.response.data.errors
                } )
            } )
    }

    getPreview () {
        axios.post ( '/api/preview', {
                body: this.state.design.body,
                header: this.state.design.header,
                footer: this.state.design.footer,
                //entity_id: 1371,
                //entity: 'invoice'
            } )
            .then ( ( response ) => {
                console.log ( 'respons', response.data.data )
                var base64str = response.data.data;

// decode base64 string, remove space for IE compatibility
                var binary = atob(base64str.replace(/\s/g, ''));
                var len = binary.length;
                var buffer = new ArrayBuffer(len);
                var view = new Uint8Array(buffer);
                for (var i = 0; i < len; i++) {
                    view[i] = binary.charCodeAt(i);
                }

// create the blob object with content-type "application/pdf"
                var blob = new Blob( [view], { type: "application/pdf" });
                var url = URL.createObjectURL(blob);

                /*const file = new Blob (
                    [ response.data.data ],
                    { type: 'application/pdf' } ) */
                //const fileURL = URL.createObjectURL ( file )

                this.setState ( { obj_url: url }, () => URL.revokeObjectURL ( url ) )
            } )
            .catch ( ( error ) => {
                this.setState ( {
                    errors: error.response.data.errors
                } )
            } )
    }

    toggle () {
        if ( this.state.modal && this.state.changesMade ) {
            if ( window.confirm ( 'Your changes have not been saved?' ) ) {
                this.setState ( { ...this.initialState } )
            }

            return
        }

        this.setState ( {
            modal: !this.state.modal,
            errors: []
        } )
    }

    render () {
        return (
            <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Edit Design
                    </ModalHeader>
                    <ModalBody>
                        <FormGroup>
                            <Label for="name">Name <span className="text-danger">*</span></Label>
                            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''}
                                   value={this.state.name}
                                   type="text"
                                   name="name"
                                   id="name"
                                   placeholder="Name" onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor ( 'name' )}
                        </FormGroup>

                        <FormGroup>
                            <Label for="name">Design <span className="text-danger">*</span></Label>
                            <DesignDropdown handleInputChanges={this.switchDesign}/>
                        </FormGroup>

                        <FormGroup>
                            <Label for="name">Header <span className="text-danger">*</span></Label>
                            <CKEditor
                                data={this.state.design.header}
                                editor={ClassicEditor}
                                config={{
                                    toolbar: ['heading', '|', 'bold', 'italic', 'blockQuote', 'link', 'numberedList', 'bulletedList', 'imageUpload', 'insertTable',
                                        'tableColumn', 'tableRow', 'mergeTableCells', 'mediaEmbed', '|', 'undo', 'redo']
                                }}
                                onInit={editor => {
                                    // You can store the "editor" and use when it is needed.
                                    console.log('Editor is ready to use!', editor)
                                }}
                                onChange={(event, editor) => {
                                    const data = editor.getData()
                                    alert(data)
                                    this.setState(prevState => ({
                                        design: { // object that we want to update
                                            ...prevState.design, // keep all other key-value pairs
                                            header: data // update the value of specific key
                                        }
                                    }), () => console.log('design', this.state.design))
                                }}
                            />
                        </FormGroup>


                        <FormGroup>
                            <Label for="name">Body <span className="text-danger">*</span></Label>
                            <CKEditor
                                data={this.state.design.body}
                                editor={ClassicEditor}
                                config={{
                                    toolbar: ['heading', '|', 'bold', 'italic', 'blockQuote', 'link', 'numberedList', 'bulletedList', 'imageUpload', 'insertTable',
                                        'tableColumn', 'tableRow', 'mergeTableCells', 'mediaEmbed', '|', 'undo', 'redo']
                                }}
                                onInit={editor => {
                                    // You can store the "editor" and use when it is needed.
                                    console.log('Editor is ready to use!', editor)
                                }}
                                onChange={(event, editor) => {
                                    const data = editor.getData()
                                    this.setState(prevState => ({
                                        design: { // object that we want to update
                                            ...prevState.design, // keep all other key-value pairs
                                            body: data // update the value of specific key
                                        }
                                    }), () => console.log('design', this.state.design))
                                }}
                            />
                        </FormGroup>

                        <FormGroup>
                            <Label for="name">Footer <span className="text-danger">*</span></Label>
                            <CKEditor
                                data={this.state.design.footer}
                                editor={ClassicEditor}
                                config={{
                                    toolbar: ['heading', '|', 'bold', 'italic', 'blockQuote', 'link', 'numberedList', 'bulletedList', 'imageUpload', 'insertTable',
                                        'tableColumn', 'tableRow', 'mergeTableCells', 'mediaEmbed', '|', 'undo', 'redo']
                                }}
                                onInit={editor => {
                                    // You can store the "editor" and use when it is needed.
                                    console.log('Editor is ready to use!', editor)
                                }}
                                onChange={(event, editor) => {
                                    const data = editor.getData()
                                    this.setState(prevState => ({
                                        design: { // object that we want to update
                                            ...prevState.design, // keep all other key-value pairs
                                            footer: data // update the value of specific key
                                        }
                                    }), () => console.log('design', this.state.design))
                                }}
                            />
                        </FormGroup>

                        <div className="embed-responsive embed-responsive-21by9">
                            <iframe className="embed-responsive-item" id="viewer" src={this.state.obj_url}/>
                        </div>

                        <Button onClick={this.getPreview} color="primary">Preview</Button>
                    </ModalBody>

                    <ModalFooter>
                        <Button color="primary" onClick={this.handleClick.bind(this)}>Add</Button>
                        <Button color="secondary" onClick={this.toggle}>Close</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}

export default EditDesign
