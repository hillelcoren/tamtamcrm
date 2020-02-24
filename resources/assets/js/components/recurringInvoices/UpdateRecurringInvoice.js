import React, { Component } from 'react'
import moment from 'moment'
import { Button, FormGroup, Input, Label, Modal, ModalBody, ModalHeader, ModalFooter, DropdownItem } from 'reactstrap'
import axios from 'axios'
import FormBuilder from '../accounts/FormBuilder'

class UpdateRecurringInvoice extends Component {
    constructor (props, context) {
        super(props, context)
        this.state = {
            errors: [],
            is_recurring: false,
            changesMade: false,
            id: this.props.invoice.id,
            invoice_id: this.props.invoice && this.props.invoice.invoice_id ? this.props.invoice.invoice_id : 0,
            customer_id: this.props.invoice && this.props.invoice.customer_id ? this.props.invoice.customer_id : 0,
            finance_type: this.props.finance_type ? this.props.finance_type : 1,
            start_date: this.props.invoice && this.props.invoice.start_date ? this.props.invoice.start_date : moment(new Date()).format('YYYY-MM-DD'),
            end_date: this.props.invoice && this.props.invoice.end_date ? this.props.invoice.end_date : moment(new Date()).format('YYYY-MM-DD'),
            recurring_due_date: this.props.invoice && this.props.invoice.recurring_due_date ? this.props.invoice.recurring_due_date : moment(new Date()).format('YYYY-MM-DD'),
            frequency: this.props.invoice && this.props.invoice.frequency ? this.props.invoice.frequency : 1,
            custom_value1: this.props.invoice.custom_value1,
            custom_value2: this.props.invoice.custom_value2,
            custom_value3: this.props.invoice.custom_value3,
            custom_value4: this.props.invoice.custom_value4,
            public_notes: this.props.invoice.public_notes,
            private_notes: this.props.invoice.private_notes
        }

        this.initialState = this.state
        this.handleInput = this.handleInput.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.toggle = this.toggle.bind(this)
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
        axios.put(`/api/recurring-invoice/${this.state.id}`, {
            start_date: this.state.start_date,
            invoice_id: this.state.invoice_id,
            customer_id: this.state.customer_id,
            end_date: this.state.end_date,
            recurring_due_date: this.state.recurring_due_date,
            frequency: this.state.frequency,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4,
            public_notes: this.state.public_notes,
            private_notes: this.state.private_notes
        })
            .then((response) => {
                this.toggle()
                const newUser = response.data
                this.props.categories.push(newUser)
                this.props.action(this.props.categories)
            })
            .catch((error) => {
                alert(error)
                this.setState({
                    errors: error.response.data.errors
                })
            })
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })

        if (this.props.setRecurring) {
            this.props.setRecurring(JSON.stringify(this.state))
        }
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
        const inlineClass = this.props ? 'mb-4' : 'form-inline mb-4'
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        const form = (
            <div className={inlineClass}>
                <FormGroup>
                    <Label for="start_date">Start Date(*):</Label>
                    <Input value={this.state.start_date} type="date" id="start_date" name="start_date"
                        onChange={this.handleInput}/>
                    {this.renderErrorFor('start_date')}
                </FormGroup>

                <FormGroup>
                    <Label for="end_date">End Date(*):</Label>
                    <Input value={this.state.end_date} type="date" id="end_date" name="end_date"
                        onChange={this.handleInput}/>
                    {this.renderErrorFor('end_date')}
                </FormGroup>

                <FormGroup>
                    <Label for="recurring_due_date">Recurring Due Date(*):</Label>
                    <Input value={this.state.recurring_due_date} type="date" id="recurring_due_date"
                        name="recurring_due_date" onChange={this.handleInput}/>
                    {this.renderErrorFor('recurring_due_date')}
                </FormGroup>

                <FormGroup>
                    <Label>Frequency</Label>
                    <Input
                        value={this.state.frequency}
                        type='select'
                        name='frequency'
                        id='frequency'
                        onChange={this.handleInput}
                    >
                        <option value="" />
                        <option value="1">Daily</option>
                        <option value="2">Weekly</option>
                        <option value="3">Every 2 weeks</option>
                        <option value="4">Every 4 weeks</option>
                        <option value="5">Monthly</option>
                        <option value="6">Every 2 months</option>
                        <option value="7">Every 3 months</option>
                        <option value="8">Every 4 months</option>
                        <option value="9">Every 6 months</option>
                        <option value="10">Annually</option>
                        <option value="11">Every 2 years</option>
                        <option value="12">Every 3 years</option>
                    </Input>
                </FormGroup>

                <FormGroup>
                    <Label for="public_notes">Public Notes(*):</Label>
                    <Input value={this.state.public_notes} type="text" id="public_notes" name="public_notes"
                        onChange={this.handleInput}/>
                    {this.renderErrorFor('public_notes')}
                </FormGroup>

                <FormGroup>
                    <Label for="private_notes">Private Notes(*):</Label>
                    <Input value={this.state.private_notes} type="text" id="private_notes" name="private_notes"
                        onChange={this.handleInput}/>
                    {this.renderErrorFor('private_notes')}
                </FormGroup>

                {customForm}
            </div>
        )

        return this.props.modal === true
            ? <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Invoice
                    </ModalHeader>

                    <ModalBody>
                        {form}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="primary" onClick={this.handleClick.bind(this)}>Add</Button>
                        <Button color="secondary" onClick={this.toggle}>Close</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
            : form
    }
}

export default UpdateRecurringInvoice
