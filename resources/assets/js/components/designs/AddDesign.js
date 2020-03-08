import React from 'react'
import {
    Button, Modal, ModalHeader, ModalBody, ModalFooter, Input, FormGroup, Label, Card,
    CardHeader,
    CardBody,
    Nav,
    NavItem,
    NavLink,
    TabContent, TabPane
} from 'reactstrap'
import axios from 'axios'
import AddButtons from '../common/AddButtons'
import DesignDropdown from '../common/DesignDropdown'
import CKEditor from '@ckeditor/ckeditor5-react'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

class AddDesign extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            name: '',
            id: null,
            is_custom: true,
            design: {
                header: '',
                body: '',
                footer: '',
                includes: '',
                product: '',
                task: ''
            },
            obj_url: null,
            activeTab: '1',
            loading: false,
            errors: []
        }

        this.toggleTabs = this.toggleTabs.bind(this)
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.getPreview = this.getPreview.bind(this)
        this.switchDesign = this.switchDesign.bind(this)
        this.resetCounters = this.resetCounters.bind(this)
        this.update = this.update.bind(this)
        this.save = this.save.bind(this)
    }

    componentDidMount () {
        if (localStorage.hasOwnProperty('designForm')) {
            const storedValues = JSON.parse(localStorage.getItem('designForm'))
            this.setState({ ...storedValues }, () => console.log('new state', this.state))
        }
    }

    toggleTabs (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab }, () => {
                if (this.state.activeTab === '2') {
                    this.getPreview()
                }
            })
        }
    }

    handleChange (el) {
        const inputName = el.target.name
        const inputValue = el.target.value

        const statusCopy = Object.assign({}, this.state)
        statusCopy.design[inputName].value = inputValue

        this.setState(statusCopy)
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

    getFormData () {
        return {
            name: this.state.name,
            design: this.state.design
        }
    }

    save () {
        axios.post('/api/designs', this.getFormData())
            .then((response) => {
                const newUser = response.data
                this.props.designs.push(newUser)
                this.props.action(this.props.designs)
                localStorage.removeItem('designForm')
                this.setState({
                    name: null
                })
                // this.toggle ()
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
            })
    }

    update () {
        axios.put(`/api/designs/${this.state.id}`, this.getFormData())
            .then((response) => {
                const index = this.props.designs.findIndex(design => design.id === parseInt(this.state.id))
                this.props.designs[index] = response.data
                this.props.action(this.props.designs)
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
            })
    }

    handleClick () {
        if (this.state.id !== null) {
            this.update()
            return
        }

        this.save()
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

    getPreview () {
        axios.post('/api/preview', {
            design: {
                name: this.state.name,
                body: this.state.design.body,
                header: this.state.design.header,
                footer: this.state.design.footer,
                includes: this.state.design.includes,
                product: this.state.design.product,
                task: this.state.design.task
            },
            entity_id: 1529,
            entity: 'invoice'
        })
            .then((response) => {
                console.log('respons', response.data.data)
                var base64str = response.data.data

                // decode base64 string, remove space for IE compatibility
                var binary = atob(base64str.replace(/\s/g, ''))
                var len = binary.length
                var buffer = new ArrayBuffer(len)
                var view = new Uint8Array(buffer)
                for (var i = 0; i < len; i++) {
                    view[i] = binary.charCodeAt(i)
                }

                // create the blob object with content-type "application/pdf"
                var blob = new Blob([view], { type: 'application/pdf' })
                var url = URL.createObjectURL(blob)

                /* const file = new Blob (
                 [ response.data.data ],
                 { type: 'application/pdf' } ) */
                // const fileURL = URL.createObjectURL ( file )

                this.setState({ obj_url: url }, () => URL.revokeObjectURL(url))
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
            })
    }

    resetCounters () {
        this.setState({ name: '', id: null, design: { header: '', body: '', footer: '' }, obj_url: null, is_custom: true })
    }

    switchDesign (design) {
        console.log('design', design)
        this.setState({
            design: design[0].design,
            name: design[0].name,
            id: design[0].id,
            is_custom: false
        })
    }

    render () {
        const title = this.state.is_custom === true ? <FormGroup>
            <Label for="name">Name <span className="text-danger">*</span></Label>
            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''} type="text" name="name"
                id="name" value={this.state.name} placeholder="Name"
                onChange={this.handleInput.bind(this)}/>
            {this.renderErrorFor('name')}
        </FormGroup> : <FormGroup>
            <Label for="name">Name <span className="text-danger">*</span></Label>
            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''} type="text" name="name"
                id="name" disabled="disabled" value={this.state.name} placeholder="Name"
                onChange={this.handleInput.bind(this)}/>
            {this.renderErrorFor('name')}
        </FormGroup>
        return (
            <React.Fragment>
                <AddButtons toggle={this.toggle}/>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Add Design
                    </ModalHeader>
                    <ModalBody>
                        <Nav tabs>
                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '1' ? 'active' : ''}
                                    onClick={() => { this.toggleTabs('1') }}>
                            Edit
                                </NavLink>
                            </NavItem>
                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '2' ? 'active' : ''}
                                    onClick={() => { this.toggleTabs('2') }}>
                            Preview
                                </NavLink>
                            </NavItem>
                        </Nav>

                        <TabContent activeTab={this.state.activeTab}>
                            <TabPane tabId="1">

                                <Card>
                                    <CardHeader>{this.state.template_type}</CardHeader>
                                    <CardBody>
                                        {title}

                                        <FormGroup>
                                            <Label for="name">Design <span className="text-danger">*</span></Label>
                                            <DesignDropdown resetCounters={this.resetCounters} handleInputChanges={this.switchDesign}/>
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
                                    </CardBody>
                                </Card>
                            </TabPane>

                            <TabPane tabId="2">

                                <Card>
                                    <CardHeader>Preview</CardHeader>
                                    <CardBody>
                                        <div className="embed-responsive embed-responsive-21by9">
                                            <iframe className="embed-responsive-item" id="viewer" src={this.state.obj_url}/>
                                        </div>
                                    </CardBody>
                                </Card>
                            </TabPane>
                        </TabContent>
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
