import React from 'react'
import {
    Button,
    Modal,
    ModalHeader,
    ModalBody,
    ModalFooter,
    Input,
    Label,
    FormGroup, CardHeader, CardBody, Card
} from 'reactstrap'
import axios from 'axios'
import CustomerDropdown from '../common/CustomerDropdown'
import FormBuilder from '../accounts/FormBuilder'
import AddButtons from '../common/AddButtons'
import DesignDropdown from '../common/DesignDropdown'

class AddCredit extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            invitations: [],
            contacts: [],
            total: 0,
            customer_id: null,
            custom_value1: '',
            custom_value2: '',
            custom_value3: '',
            private_notes: '',
            public_notes: '',
            custom_value4: '',
            design_id: null,
            loading: false,
            errors: [],
            message: ''
        }
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleInput = this.handleInput.bind(this)
        this.handleContactChange = this.handleContactChange.bind(this)
    }

    componentDidMount () {
        if (localStorage.hasOwnProperty('creditForm')) {
            const storedValues = JSON.parse(localStorage.getItem('creditForm'))
            this.setState({ ...storedValues }, () => console.log('new state', this.state))
        }
    }

    handleInput (e) {
        if (e.target.name === 'customer_id') {
            const index = this.props.customers.findIndex(customer => customer.id === parseInt(e.target.value))
            const customer = this.props.customers[index]
            const contacts = customer.contacts ? customer.contacts : []
            this.setState({
                customer_id: e.target.value,
                contacts: contacts
            }, () => localStorage.setItem('creditForm', JSON.stringify(this.state)))

            return
        }

        this.setState({
            [e.target.name]: e.target.value
        })
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

    handleContactChange (e) {
        const invitations = this.state.invitations

        const contact = e.target.value

        // check if the check box is checked or unchecked
        if (e.target.checked) {
            // add the numerical value of the checkbox to options array
            invitations.push({ client_contact_id: contact })
        } else {
            // or remove the value from the unchecked checkbox from the array
            const index = invitations.findIndex(contact => contact.client_contact_id === contact)
            invitations.splice(index, 1)
        }

        // update the state with the new array of options
        this.setState({ invitations: invitations }, () => console.log('invitations', invitations))
    }

    handleClick () {
        axios.post('/api/credit', {
            total: this.state.total,
            design_id: this.state.design_id,
            balance: this.state.total,
            public_notes: this.state.public_notes,
            private_notes: this.state.private_notes,
            customer_id: this.state.customer_id,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4,
            invitations: this.state.invitations
        })
            .then((response) => {
                const newUser = response.data
                this.props.credits.push(newUser)
                this.props.action(this.props.credits)
                localStorage.removeItem('creditForm')
                this.setState({
                    contacts: [],
                    total: 0,
                    customer_id: null,
                    custom_value1: '',
                    custom_value2: '',
                    public_notes: '',
                    private_notes: '',
                    custom_value3: '',
                    custom_value4: '',
                    design_id: null,
                    loading: false,
                    errors: []
                })
                this.toggle()
            })
            .catch((error) => {
                if (error.response.data.errors) {
                    this.setState({
                        errors: error.response.data.errors
                    })
                } else {
                    this.setState({ message: error.response.data })
                }
            })
    }

    toggle () {
        this.setState({
            modal: !this.state.modal,
            errors: []
        }, () => {
            if (!this.state.modal) {
                this.setState({
                    contacts: [],
                    total: 0,
                    customer_id: null,
                    custom_value1: '',
                    custom_value2: '',
                    design_id: null,
                    public_notes: '',
                    private_notes: '',
                    custom_value3: '',
                    custom_value4: '',
                    loading: false,
                    errors: []
                }, () => localStorage.removeItem('creditForm'))
            }
        })
    }

    render () {
        const { message } = this.state
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        return (
            <React.Fragment>
                <AddButtons toggle={this.toggle}/>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Add Credit
                    </ModalHeader>
                    <ModalBody>
                        {message && <div className="alert alert-danger" role="alert">
                            {message}
                        </div>}

                        <Card>
                            <CardHeader>Details</CardHeader>
                            <CardBody>
                                <FormGroup className="mb-3">
                                    <Label>Amount</Label>
                                    <Input value={this.state.total}
                                        className={this.hasErrorFor('total') ? 'is-invalid' : ''}
                                        type="text" name="total"
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('total')}
                                </FormGroup>

                                <FormGroup className="mb-3">
                                    <Label>Design</Label>
                                    <DesignDropdown name="design_id" handleChange={this.handleInput.bind(this)}
                                        design={this.state.design_id}/>
                                </FormGroup>

                                {customForm}
                            </CardBody>
                        </Card>

                        <Card>
                            <CardHeader>Invitations</CardHeader>
                            <CardBody>
                                <FormGroup className="mb-3">
                                    <Label>Customer</Label>

                                    <CustomerDropdown
                                        customer={this.state.customer_id}
                                        renderErrorFor={this.renderErrorFor}
                                        handleInputChanges={this.handleInput}
                                        customers={this.props.customers}
                                    />
                                    {this.renderErrorFor('customer_id')}
                                </FormGroup>

                                {this.state.contacts.length && this.state.contacts.map((contact, index) => (
                                    <FormGroup key={index} check>
                                        <Label check>
                                            <Input value={contact.id} onChange={this.handleContactChange}
                                                type="checkbox"/> {`${contact.first_name} ${contact.last_name}`}
                                        </Label>
                                    </FormGroup>
                                ))
                                }
                            </CardBody>
                        </Card>

                        <Card>
                            <CardHeader>Notes</CardHeader>
                            <CardBody>
                                <FormGroup className="mb-3">
                                    <Label>Public Notes</Label>
                                    <Input value={this.state.public_notes}
                                        className={this.hasErrorFor('total') ? 'is-invalid' : ''}
                                        type="textarea" name="public_notes"
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('public_notes')}
                                </FormGroup>

                                <FormGroup className="mb-3">
                                    <Label>Private Notes</Label>
                                    <Input value={this.state.private_notes}
                                        className={this.hasErrorFor('total') ? 'is-invalid' : ''}
                                        type="textarea" name="private_notes"
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('private_notes')}
                                </FormGroup>
                            </CardBody>
                        </Card>
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

export default AddCredit
