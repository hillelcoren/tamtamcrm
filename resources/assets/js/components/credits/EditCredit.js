import React from 'react'
import {
    Button,
    Modal,
    ModalHeader,
    ModalBody,
    ModalFooter,
    Input,
    Label,
    InputGroupAddon,
    InputGroupText,
    InputGroup,
    DropdownItem,
    Dropdown,
    DropdownToggle,
    DropdownMenu
} from 'reactstrap'
import axios from 'axios'
import CustomerDropdown from '../common/CustomerDropdown'
import FormBuilder from '../accounts/FormBuilder'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'
import DesignDropdown from '../common/DesignDropdown'

class EditCredit extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            id: this.props.credit.id,
            showSuccessMessage: false,
            showErrorMessage: false,
            total: this.props.credit.total,
            customer_id: this.props.credit.customer_id,
            design_id: this.props.credit.design_id,
            custom_value1: this.props.credit.custom_value1,
            public_notes: this.props.credit.public_notes,
            private_notes: this.props.credit.private_notes,
            custom_value2: this.props.credit.custom_value2,
            custom_value3: this.props.credit.custom_value3,
            custom_value4: this.props.credit.custom_value4,
            loading: false,
            dropdownOpen: false,
            changesMade: false,
            errors: [],
            message: ''
        }

        this.initialState = this.state
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleInput = this.handleInput.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
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

    downloadPdf (response, id) {
        const linkSource = `data:application/pdf;base64,${response.data.data}`
        const downloadLink = document.createElement('a')
        const fileName = `credit_${id}.pdf`

        downloadLink.href = linkSource
        downloadLink.download = fileName
        downloadLink.click()
    }

    changeStatus (action) {
        if (!this.state.id) {
            return false
        }

        const data = this.getFormData()

        axios.post(`/api/credit/${this.state.id}/${action}`, data)
            .then((response) => {
                let message = `${action} completed successfully`

                if (action === 'clone_to_credit') {
                    this.props.credits.push(response.data)
                    this.props.action(this.props.credits)
                    message = `Credit was cloned successfully. Quote ${response.data.number} has been created`
                }

                if (action === 'download') {
                    this.downloadPdf(response, this.state.id)
                    message = 'The PDF file has been downloaded'
                }

                if (action === 'clone_to_quote') {
                    message = `The credit was successfully converted to a quote. Quote ${response.data.number} has been created`
                }

                if (action === 'mark_sent') {
                    message = 'The quote has been marked as sent'
                }

                if (action === 'email') {
                    message = 'The email has been sent successfully'
                }

                this.setState({
                    showSuccessMessage: message,
                    showErrorMessage: false
                })
            })
            .catch((error) => {
                this.setState({
                    showErrorMessage: true,
                    showSuccessMessage: false
                })
                console.warn(error)
            })
    }

    getFormData () {
        const data = {
            total: this.state.total,
            customer_id: this.state.customer_id,
            design_id: this.state.design_id,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4,
            public_notes: this.state.public_notes,
            private_notes: this.state.private_notes
        }

        return data
    }

    handleClick () {
        const data = this.getFormData()
        axios.put(`/api/credit/${this.state.id}`, data)
            .then((response) => {
                const index = this.props.credits.findIndex(credit => credit.id === this.props.credit.id)
                this.props.credits[index] = response.data
                this.props.action(this.props.credits)
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

    render () {
        const { message } = this.state
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        const sendEmailButton = <DropdownItem className="primary" onClick={() => this.changeStatus('email')}>Send
            Email</DropdownItem>

        const downloadButton = <DropdownItem className="primary"
            onClick={() => this.changeStatus('download')}>Download</DropdownItem>

        const cloneToQuote = <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_quote')}>Clone
            To
            Quote</DropdownItem>

        const cloneToCredit = <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_credit')}>Clone
            To
            Credit</DropdownItem>

        const changeStatusButton = <DropdownItem color="primary" onClick={() => this.changeStatus('mark_sent')}>Mark
            Sent</DropdownItem>

        const dropdownMenu = <Dropdown isOpen={this.state.dropdownOpen} toggle={this.toggleMenu}>
            <DropdownToggle caret>
                Actions
            </DropdownToggle>

            <DropdownMenu>
                <DropdownItem header>Header</DropdownItem>
                {changeStatusButton}
                {downloadButton}
                {sendEmailButton}
                {cloneToCredit}
                {cloneToQuote}
            </DropdownMenu>
        </Dropdown>

        const successMessage = this.state.showSuccessMessage !== false && this.state.showSuccessMessage !== ''
            ? <SuccessMessage message={this.state.showSuccessMessage}/> : null
        const errorMessage = this.state.showErrorMessage === true
            ? <ErrorMessage message="Something went wrong"/> : null

        return (
            <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Update Credit
                    </ModalHeader>
                    <ModalBody>
                        {dropdownMenu}
                        {successMessage}
                        {errorMessage}

                        {message && <div className="alert alert-danger" role="alert">
                            {message}
                        </div>}

                        <Label>Amount</Label>
                        <InputGroup className="mb-3">
                            <InputGroupAddon addonType="prepend">
                                <InputGroupText><i className="fa fa-user-o"/></InputGroupText>
                            </InputGroupAddon>
                            <Input value={this.state.total} className={this.hasErrorFor('total') ? 'is-invalid' : ''}
                                type="text" name="total"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('total')}
                        </InputGroup>

                        <Label>Design</Label>
                        <InputGroup className="mb-3">
                            <DesignDropdown name="design_id" design={this.state.design}
                                handleChange={this.handleInput.bind(this)}/>
                        </InputGroup>

                        <Label>Customer</Label>
                        <InputGroup className="mb-3">
                            <InputGroupAddon addonType="prepend">
                                <InputGroupText><i className="fa fa-user-o"/></InputGroupText>
                            </InputGroupAddon>
                            <CustomerDropdown
                                customer={this.state.customer_id}
                                renderErrorFor={this.renderErrorFor}
                                handleInputChanges={this.handleInput}
                                customers={this.props.customers}
                            />
                            {this.renderErrorFor('customer_id')}
                        </InputGroup>

                        <Label>Public Notes</Label>
                        <InputGroup className="mb-3">
                            <InputGroupAddon addonType="prepend">
                                <InputGroupText><i className="fa fa-user-o"/></InputGroupText>
                            </InputGroupAddon>
                            <Input value={this.state.public_notes}
                                className={this.hasErrorFor('public_notes') ? 'is-invalid' : ''}
                                type="text" name="public_notes"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('public_notes')}
                        </InputGroup>

                        <Label>Private Notes</Label>
                        <InputGroup className="mb-3">
                            <InputGroupAddon addonType="prepend">
                                <InputGroupText><i className="fa fa-user-o"/></InputGroupText>
                            </InputGroupAddon>
                            <Input value={this.state.private_notes}
                                className={this.hasErrorFor('private_notes') ? 'is-invalid' : ''}
                                type="text" name="private_notes"
                                onChange={this.handleInput.bind(this)}/>
                            {this.renderErrorFor('private_notes')}
                        </InputGroup>

                        {customForm}

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

export default EditCredit
