import React from 'react'
import {
    Button, Modal, ModalHeader, ModalBody, ModalFooter, Input, Label, FormGroup, DropdownItem, Dropdown,
    DropdownToggle,
    DropdownMenu
} from 'reactstrap'
import axios from 'axios'
import CustomerDropdown from '../common/CustomerDropdown'
import PaymentTypeDropdown from '../common/PaymentTypeDropdown'
import moment from 'moment'
import FormBuilder from '../accounts/FormBuilder'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'
import InvoiceLine from './InvoiceLine'

class EditPayment extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            invoices: this.props.invoices,
            modal: false,
            loading: false,
            changesMade: false,
            dropdownOpen: false,
            showSuccessMessage: false,
            showErrorMessage: false,
            errors: [],
            customer_id: this.props.payment.customer_id,
            custom_value1: this.props.payment.custom_value1,
            custom_value2: this.props.payment.custom_value2,
            custom_value3: this.props.payment.custom_value3,
            custom_value4: this.props.payment.custom_value4,
            private_notes: this.props.payment.private_notes,
            invoice_id: this.props.payment.invoice_id,
            amount: this.props.payment.amount,
            payable_invoices: this.props.payment.paymentables && this.props.payment.paymentables.length ? this.props.payment.paymentables : [],
            date: this.props.payment.date ? this.props.payment.date : moment(new Date()).format('YYYY-MM-DD'),
            id: this.props.payment.id,
            type_id: this.props.payment.type_id,
            transaction_reference: this.props.payment.transaction_reference,
            message: ''
        }

        this.initialState = this.state
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.setInvoices = this.setInvoices.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleCustomerChange = this.handleCustomerChange.bind(this)
        this.handleInvoiceChange = this.handleInvoiceChange.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    handleInvoiceChange (e) {
        if (e.target.value === '') {
            return
        }

        const invoice = this.props.invoices.filter(function (invoice) {
            return invoice.id === parseInt(e.target.value)
        })

        if (!invoice.length) {
            return
        }

        this.setState({
            [e.target.name]: e.target.value,
            customer_id: invoice[0].customer_id,
            amount: invoice[0].total
        })

        this.setState({ payable_invoices: Array.from(e.target.selectedOptions, (item) => item.value) })
    }

    handleCustomerChange (e) {
        var filteredData
        if (e.target.value === '') {
            filteredData = this.props.invoices
        } else {
            filteredData = this.props.invoices.filter(function (invoice) {
                return invoice.customer_id === parseInt(e.target.value)
            })
        }

        this.setState({
            [e.target.name]: e.target.value,
            invoices: filteredData
        })
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
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

    getFormData () {
        return {
            type_id: this.state.type_id,
            invoice_id: this.state.invoice_id,
            customer_id: this.state.customer_id,
            amount: this.state.amount,
            transaction_reference: this.state.transaction_reference,
            invoices: this.state.payable_invoices,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4,
            private_notes: this.state.private_notes
        }
    }

    changeStatus (action) {
        if (!this.state.id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/payment/${this.state.id}/${action}`, data)
            .then((response) => {
                if (action === 'download') {
                    this.downloadPdf(response)
                }

                this.setState({ showSuccessMessage: true })
            })
            .catch((error) => {
                this.setState({ showErrorMessage: true })
                console.warn(error)
            })
    }

    handleClick () {
        const data = this.getFormData()
        axios.put(`/api/payments/${this.state.id}`, data)
            .then((response) => {
                this.initialState = this.state
                const index = this.props.payments.findIndex(payment => payment.id === this.props.payment.id)
                this.props.payments[index].name = this.state.name
                this.props.payments[index].description = this.state.description
                this.props.action(this.props.payments)
                this.toggle()
            })
            .catch((error) => {
                if (error.response.data.message) {
                    this.setState({ message: error.response.data.message })
                }

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
        if (this.state.modal && this.state.changesMade) {
            if (window.confirm('Your changes have not been saved?')) {
                this.setState({ ...this.initialState })
            }

            return
        }

        this.setState({
            modal: !this.state.modal,
            errors: []
        })
    }

    setAmount (amount) {
        this.setState({ amount: amount }, () => localStorage.setItem('paymentForm', JSON.stringify(this.state)))
    }

    setInvoices (payableInvoices) {
        this.setState({ payable_invoices: payableInvoices }, () => localStorage.setItem('paymentForm', JSON.stringify(this.state)))
    }

    render () {
        const { message } = this.state
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        const sendEmailButton = <DropdownItem className="primary" onClick={() => this.changeStatus('email')}>Send
            Email</DropdownItem>

        const downloadButton = this.state.status_id === 1
            ? <DropdownItem className="primary"
                onClick={() => this.changeStatus('download')}>Download</DropdownItem> : null

        const deleteButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('delete')}>Delete</DropdownItem> : null

        const archiveButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('archive')}>Archive</DropdownItem> : null

        const cloneButton =
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_invoice')}>Clone To
                Invoice</DropdownItem>

        const dropdownMenu = this.state.id
            ? <Dropdown isOpen={this.state.dropdownOpen} toggle={this.toggleMenu}>
                <DropdownToggle caret>
                    Actions
                </DropdownToggle>

                <DropdownMenu>
                    <DropdownItem header>Header</DropdownItem>
                    {sendEmailButton}
                    {downloadButton}
                    {deleteButton}
                    {archiveButton}
                    {cloneButton}
                </DropdownMenu>
            </Dropdown> : null

        const successMessage = this.state.showSuccessMessage === true
            ? <SuccessMessage message="Invoice was updated successfully"/> : null
        const errorMessage = this.state.showErrorMessage === true
            ? <ErrorMessage message="Something went wrong"/> : null

        return (
            <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Update Payment
                    </ModalHeader>
                    <ModalBody>

                        {message && <div className="alert alert-danger" role="alert">
                            {message}
                        </div>}

                        {dropdownMenu}
                        {successMessage}
                        {errorMessage}

                        <FormGroup className="mb-3">
                            <Label>Amount</Label>
                            <Input value={this.state.amount}
                                className={this.hasErrorFor('amount') ? 'is-invalid' : ''} type="text" name="amount"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('amount')}
                        </FormGroup>

                        <FormGroup className="mr-2">
                            <Label for="date">Date(*):</Label>
                            <Input className={this.hasErrorFor('date') ? 'is-invalid' : ''} value={this.state.date}
                                type="date" id="date" name="date"
                                onChange={this.handleInput}/>
                            {this.renderErrorFor('due_date')}
                        </FormGroup>

                        <FormGroup className="mb-3">
                            <Label>Transaction Reference</Label>
                            <Input value={this.state.transaction_reference}
                                className={this.hasErrorFor('transaction_reference') ? 'is-invalid' : ''} type="text"
                                name="transaction_reference"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('transaction_reference')}
                        </FormGroup>

                        <PaymentTypeDropdown
                            name="type_id"
                            errors={this.state.errors}
                            handleInputChanges={this.handleInput}
                        />

                        <FormGroup>
                            <Label>Customer</Label>
                            <CustomerDropdown
                                customers={this.props.customers}
                                customer={this.state.customer_id}
                                name="customer_id"
                                errors={this.state.errors}
                                handleInputChanges={this.handleCustomerChange}
                            />
                        </FormGroup>

                        <InvoiceLine lines={this.state.payable_invoices} handleAmountChange={this.setAmount} errors={this.state.errors}
                            invoices={this.props.invoices}
                            customerChange={this.handleCustomerChange} onChange={this.setInvoices}/>

                        <FormGroup className="mb-3">
                            <Label>Notes</Label>
                            <Input value={this.state.private_notes}
                                className={this.hasErrorFor('private_notes') ? 'is-invalid' : ''} type="text"
                                name="private_notes"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('private_notes')}
                        </FormGroup>

                        {customForm}
                    </ModalBody>

                    <ModalFooter>
                        <Button color="primary" onClick={this.handleClick.bind(this)}>Update</Button>
                        <Button color="secondary" onClick={this.toggle}>Close</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}

export default EditPayment
