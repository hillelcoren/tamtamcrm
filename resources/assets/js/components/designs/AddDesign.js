import React from 'react'
import { Button, Modal, ModalHeader, ModalBody, ModalFooter, Input, FormGroup, Label } from 'reactstrap'
import axios from 'axios'
import AddButtons from '../common/AddButtons'
import DesignDropdown from '../common/DesignDropdown'
import CKEditor from '@ckeditor/ckeditor5-react'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

import {config} from '../common/HtmlEditor'

class AddDesign extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            name: '',
            design: { header: null, footer: null, body: null },
            loading: false,
            errors: []
        }

        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
    }

    componentDidMount () {
        if (localStorage.hasOwnProperty('designForm')) {
            const storedValues = JSON.parse(localStorage.getItem('designForm'))
            this.setState({ ...storedValues }, () => console.log('new state', this.state))
        }
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value
        }, () => localStorage.setItem('designForm', JSON.stringify(this.state)))
    }

    hasErrorFor (field) {
        return !!this.state.errors[field]
    }

    renderErrorFor (field) {
        if (this.hasErrorFor(field)) {
            return (
                <span className='invalid-feedback'>
                    <strong>{this.state.errors[field][0]}</strong>
                </span>
            )
        }
    }

    handleClick () {
        axios.post('/api/designs', {
            name: this.state.name,
            design: this.state.design
        })
            .then((response) => {
                const newUser = response.data
                this.props.designs.push(newUser)
                this.props.action(this.props.designs)
                localStorage.removeItem('designForm')
                this.setState({
                    name: null
                })
                this.toggle()
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
            })
    }

    toggle () {
        this.setState({
            modal: !this.state.modal,
            errors: []
        }, () => {
            if (!this.state.modal) {
                this.setState({
                    name: null,
                    icon: null
                }, () => localStorage.removeItem('designForm'))
            }
        })
    }

    render () {
        return (
            <React.Fragment>
                <AddButtons toggle={this.toggle}/>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Add Design
                    </ModalHeader>
                    <ModalBody>
                        <FormGroup>
                            <Label for="name">Name <span className="text-danger">*</span></Label>
                            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''} type="text" name="name"
                                id="name" value={this.state.name} placeholder="Name"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('name')}
                        </FormGroup>

                        <FormGroup>
                            <Label for="name">Design <span className="text-danger">*</span></Label>
                            <DesignDropdown handleInputChanges={this.handleInput.bind(this)} />
                        </FormGroup>

                        <FormGroup>
                            <Label for="name">Header <span className="text-danger">*</span></Label>
                            <CKEditor
                                config={config}
                                editor={ClassicEditor}
                                data={this.state.design.header}
                                onInit={editor => {
                                    // You can store the "editor" and use when it is needed.
                                    console.log('Editor is ready to use!', editor)
                                }}
                                onChange={(event, editor) => {
                                    const data = editor.getData()
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
                                config={config}
                                editor={ClassicEditor}
                                data={this.state.design.body}
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
                                config={config}
                                editor={ClassicEditor}
                                data={this.state.design.footer}
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

export default AddDesign
