import React, { Component } from 'react'
import Address from './Address'
import LineItemEditor from '../common/LineItemEditor'
import axios from 'axios'
import {
    Button,
    FormGroup,
    Input,
    Label,
    Modal,
    ModalBody,
    ModalFooter,
    ModalHeader,
    Card,
    CardBody,
    CardHeader,
    Nav,
    NavItem,
    NavLink,
    TabContent,
    Dropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem,
    TabPane,
    Row,
    Col
} from 'reactstrap'
import CustomerDropdown from '../common/CustomerDropdown'
import TaxRateDropdown from '../common/TaxRateDropdown'
import CompanyDropdown from '../common/CompanyDropdown'
import 'react-dates/lib/css/_datepicker.css'
import moment from 'moment'
import AddRecurringInvoice from '../recurringInvoices/AddRecurringInvoice'
import FormBuilder from '../accounts/FormBuilder'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'
import AddButtons from '../common/AddButtons'
import FileUploads from '../attachments/FileUploads'

class EditInvoice extends Component {
    constructor (props, context) {
        super(props, context)
        this.state = {
            invitations: this.props.invoice && this.props.invoice.invitations && this.props.invoice.invitations.length ? this.props.invoice.invitations : [],
            customer_id: this.props.invoice && this.props.invoice.customer_id ? this.props.invoice.customer_id : null,
            contacts: [],
            due_date: this.props.invoice && this.props.invoice.due_date && this.props.invoice.due_date.length ? moment(this.props.invoice.due_date).format('YYYY-MM-DD') : moment(new Date()).format('YYYY-MM-DD'),
            quantity: '',
            finance_type: this.props.finance_type ? this.props.finance_type : 1,
            invoice_id: this.props.invoice && this.props.invoice.id ? this.props.invoice.id : null,
            lines: [],
            address: {},
            customerName: '',
            tax_rate_name: '',
            company_id: this.props.invoice && this.props.invoice.company_id ? this.props.invoice.company_id : null,
            status_id: this.props.invoice && this.props.invoice.status_id ? parseInt(this.props.invoice.status_id) : 1,
            customers: this.props.customers,
            tasks: [],
            errors: [],
            total: this.props.invoice && this.props.invoice.total ? this.props.invoice.total : 0,
            discount_total: this.props.invoice && this.props.invoice.discount_total ? this.props.invoice.discount_total : 0,
            tax_total: this.props.invoice && this.props.invoice.tax_total ? this.props.invoice.tax_total : 0,
            sub_total: this.props.invoice && this.props.invoice.sub_total ? this.props.invoice.sub_total : 0,
            data: this.props.invoice && this.props.invoice.line_items ? this.props.invoice.line_items : [],
            date: this.props.invoice && this.props.invoice.date ? this.props.invoice.date : moment(new Date()).format('YYYY-MM-DD'),
            partial: this.props.invoice && this.props.invoice.partial ? this.props.invoice.partial : 0,
            public_notes: this.props.invoice && this.props.invoice.public_notes ? this.props.invoice.public_notes : null,
            private_notes: this.props.invoice && this.props.invoice.private_notes ? this.props.invoice.private_notes : null,
            terms: this.props.invoice && this.props.invoice.terms ? this.props.invoice.terms : null,
            footer: this.props.invoice && this.props.invoice.footer ? this.props.invoice.footer : null,
            visible: 'collapse',
            custom_value1: this.props.invoice ? this.props.invoice.custom_value1 : '',
            custom_value2: this.props.invoice ? this.props.invoice.custom_value2 : '',
            custom_value3: this.props.invoice ? this.props.invoice.custom_value3 : '',
            custom_value4: this.props.invoice ? this.props.invoice.custom_value4 : '',
            tax: 0,
            discount: 0,
            recurring: '',
            activeTab: '1',
            po_number: this.props.invoice && this.props.invoice.po_number ? this.props.invoice.po_number : '',
            dropdownOpen: false,
            success: false,
            showSuccessMessage: false,
            showErrorMessage: false,
            width: window.innerWidth
        }

        this.updateData = this.updateData.bind(this)
        this.saveData = this.saveData.bind(this)
        this.setTotal = this.setTotal.bind(this)
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
        this.handleTaskChange = this.handleTaskChange.bind(this)
        this.handleDelete = this.handleDelete.bind(this)
        this.buildForm = this.buildForm.bind(this)
        this.createInvoice = this.createInvoice.bind(this)
        this.handleInput = this.handleInput.bind(this)
        this.handleAddFiled = this.handleAddFiled.bind(this)
        this.handleFieldChange = this.handleFieldChange.bind(this)
        this.updatePriceData = this.updatePriceData.bind(this)
        this.calculateTotals = this.calculateTotals.bind(this)
        this.handleApprove = this.handleApprove.bind(this)
        this.handleSlideClick = this.handleSlideClick.bind(this)
        this.toggleTab = this.toggleTab.bind(this)
        this.setRecurring = this.setRecurring.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.handleContactChange = this.handleContactChange.bind(this)
        this.handleWindowSizeChange = this.handleWindowSizeChange.bind(this)

        this.total = 0
    }

    toggleTab (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    componentDidMount () {
        if (this.props.task_id || this.props.invoice_id) {
            this.loadInvoice()
        } else {
            if (localStorage.hasOwnProperty('invoiceForm')) {
                const storedValues = JSON.parse(localStorage.getItem('invoiceForm'))
                this.setState({ ...storedValues }, () => console.log('new state', this.state))
            }
        }

        if (this.props.invoice && this.props.invoice.customer_id) {
            const index = this.props.customers.findIndex(customer => customer.id === this.props.invoice.customer_id)
            const customer = this.props.customers[index]
            const contacts = customer.contacts ? customer.contacts : []
            this.setState({ contacts: contacts })
        }
    }

    componentWillMount () {
        window.addEventListener('resize', this.handleWindowSizeChange)
    }

    // make sure to remove the listener
    // when the component is not mounted anymore
    componentWillUnmount () {
        window.removeEventListener('resize', this.handleWindowSizeChange)
    }

    handleWindowSizeChange () {
        this.setState({ width: window.innerWidth })
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

    handleInput (e) {
        if (e.target.name === 'customer_id') {
            const index = this.state.customers.findIndex(customer => customer.id === parseInt(e.target.value))
            const customer = this.state.customers[index]

            const contacts = customer.contacts ? customer.contacts : []

            this.setState({
                customerName: customer.name,
                contacts: contacts
            }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))

            if (customer.billing) {
                const address = customer.billing
                const objAddress = {
                    line1: address.address_1,
                    town: address.address_2,
                    county: address.city,
                    country: 'United Kingdom'
                }
                this.setState({ address: objAddress }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
            }
        }

        if (e.target.name === 'tax') {
            const name = e.target.options[e.target.selectedIndex].getAttribute('data-name')
            const rate = e.target.options[e.target.selectedIndex].getAttribute('data-rate')

            this.setState({
                tax: rate,
                tax_rate_name: name
            }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))

            return
        }

        this.setState({
            [e.target.name]: e.target.value
        }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
    }

    handleTaskChange (e) {
        axios.get(`/api/products/tasks/${this.props.task_id}/1,2`)
            .then((r) => {
                const arrLines = []
                let total = 0

                if (r.data && r.data.length) {
                    r.data.map((product) => {
                        const objLine = {
                            quantity: product.quantity,
                            product_id: product.product_id,
                            unit_price: product.price,
                            unit_discount: product.unit_discount,
                            unit_tax: product.unit_tax,
                            order_id: product.order_id
                        }

                        total += parseFloat(product.price)
                        arrLines.push(objLine)
                    })
                }

                this.setState({
                    data: arrLines,
                    total: total
                }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
            })
            .catch((e) => {
                console.warn(e)
            })
    }

    handleSlideClick (e) {
        this.setState({ is_recurring: e.target.checked })
    }

    hasErrorFor (field) {
        return !!this.state.errors[field]
    }

    loadInvoice () {
        const url = this.props.task_id ? `/api/invoice/task/${this.props.task_id}` : `/api/invoice/${this.state.invoice_id}`

        axios.get(url)
            .then((r) => {
                if (r.data) {
                    this.setState({
                        data: r.data.line_items,
                        due_date: moment(r.data.due_date).format('YYYY-MM-DD'),
                        po_number: r.data.po_number,
                        invoice_id: r.data.id,
                        customer_id: r.data.customer_id,
                        company_id: r.data.company_id,
                        public_notes: r.data.public_notes,
                        private_notes: r.data.private_notes,
                        terms: r.data.terms,
                        footer: r.data.footer,
                        status_id: parseInt(r.data.status_id)
                    }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
                }
            })
            .catch((e) => {
                console.warn(e)
            })
    }

    downloadPdf (response) {
        const linkSource = `data:application/pdf;base64,${response.data.data}`
        const downloadLink = document.createElement('a')
        const fileName = 'vct_illustration.pdf'

        downloadLink.href = linkSource
        downloadLink.download = fileName
        downloadLink.click()
    }

    changeStatus (action) {
        if (!this.state.invoice_id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/invoice/${this.state.invoice_id}/${action}`, data)
            .then((response) => {
                if (action === 'download') {
                    this.downloadPdf(response)
                }

                if (action === 'clone_to_invoice') {
                    this.props.credits.push(response.data)
                    this.props.action(this.props.credits)
                }

                this.setState({ showSuccessMessage: true })
            })
            .catch((error) => {
                this.setState({ showErrorMessage: true })
                console.warn(error)
            })
    }

    toggle () {
        this.setState({
            modal: !this.state.modal,
            errors: []
        }, () => {
            if (!this.state.modal) {
                this.setState({
                    public_notes: '',
                    tax: null,
                    tax_rate_name: '',
                    private_notes: '',
                    terms: '',
                    footer: '',
                    partial: 0,
                    invoice_id: null,
                    customer_id: null,
                    company_id: null,
                    status_id: null,
                    data: []
                }, () => localStorage.removeItem('invoiceForm'))
            }
        })
    }

    updateData (rowData) {
        this.setState(prevState => ({
            data: [...prevState.data, rowData]
        }), () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
    }

    calculateTotals () {
        let total = 0
        let discount_total = 0
        let tax_total = 0
        let sub_total = 0
        let lexieTotal = 0

        this.state.data.map((product) => {
            const quantity = product.quantity === 0 ? 1 : product.quantity

            const line_total = product.unit_price * quantity
            total += line_total
            sub_total += line_total
            lexieTotal += line_total

            if (product.unit_discount > 0 && this.state.discount === 0) {
                const n = parseFloat(total)
                const percentage = n * product.unit_discount / 100
                discount_total += percentage
                lexieTotal -= discount_total
            }

            if (product.unit_tax > 0 && this.state.tax === 0) {
                const n = parseFloat(total)
                const tax_percentage = lexieTotal * product.unit_tax / 100
                tax_total += tax_percentage
            }
        })

        if (this.state.tax > 0) {
            const tax_percentage = parseFloat(this.state.total) * parseFloat(this.state.tax) / 100
            total += tax_percentage
        }

        if (this.state.discount > 0) {
            const discount_percentage = parseFloat(this.state.total) * parseFloat(this.state.discount) / 100
            total -= discount_percentage
        }

        this.setState({
            total: total,
            discount_total: discount_total,
            tax_total: tax_total,
            sub_total: sub_total
        }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
    }

    updatePriceData (index) {
        const data = this.state.data.slice()
        const currentRow = data[index]
        const price = currentRow.unit_price
        let lexieTotal = 0

        if (price < 0) {
            return false
        }

        let total = price
        const unit_discount = currentRow.unit_discount
        const unit_tax = currentRow.unit_tax
        const uses_inclusive_taxes = false

        const quantity = currentRow.quantity

        if (quantity > 0) {
            total = price * quantity
            lexieTotal += price * quantity
        }

        if (unit_discount > 0 && this.state.discount === 0) {
            const n = parseFloat(total)
            //
            const percentage = n * unit_discount / 100
            lexieTotal -= percentage
        }

        if (unit_tax > 0 && this.state.tax === 0) {
            const tax_percentage = lexieTotal * unit_tax / 100
            currentRow.tax_total = tax_percentage

            if (uses_inclusive_taxes === false) {
                total += tax_percentage
            }
        }

        currentRow.sub_total = total

        this.setState({ data: data }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
    }

    handleFieldChange (data, row) {
        this.setState({ data: data }, () => {
            this.calculateTotals()
            this.updatePriceData(row)
        })
    }

    handleAddFiled () {
        this.setState((prevState, props) => {
            return {
                data: this.state.data.concat({
                    unit_discount: 0,
                    unit_tax: 0,
                    quantity: 0,
                    unit_price: 0,
                    product_id: 0
                })
            }
        })
    }

    handleDelete (idx) {
        if (this.state.data[idx] && this.state.data[idx].order_id) {
            axios.put(`/api/orders/${this.state.data[idx].order_id}`, { status: 1 })
                .then((response) => {
                    this.setState({
                        showSuccessMessage: true,
                        showErrorMessage: false
                    })
                })
                .catch((error) => {
                    this.setState({
                        errors: error.response.data.errors,
                        showErrorMessage: true,
                        showSuccessMessage: false
                    })

                    console.warn(error)
                })
        }

        const newTasks = this.state.data.filter((task, tIndex) => {
            return idx !== tIndex
        })

        this.setState({ data: newTasks })
    }

    setTotal (total) {
        this.total = total
    }

    getFormData () {
        const data = {
            tax_rate: this.state.tax,
            tax_rate_name: this.state.tax_rate_name,
            invoice_id: this.state.invoice_id,
            task_id: this.props.task_id,
            due_date: this.state.due_date,
            customer_id: this.state.customer_id,
            company_id: this.state.company_id,
            line_items: this.state.data,
            total: this.state.total,
            sub_total: this.state.sub_total,
            tax_total: this.state.tax_total,
            discount_total: this.state.discount_total,
            public_notes: this.state.public_notes,
            private_notes: this.state.private_notes,
            po_number: this.state.po_number,
            terms: this.state.terms,
            footer: this.state.footer,
            date: this.state.date,
            partial: this.state.partial,
            recurring: this.state.recurring,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4,
            invitations: this.state.invitations
        }

        return data
    }

    saveData () {
        const data = this.getFormData()
        if (!this.state.invoice_id) {
            return this.createInvoice(data)
        }

        const url = this.state.finance_type === 2 ? `/api/quote/${this.state.invoice_id}` : `/api/invoice/${this.state.invoice_id}`

        axios.put(url, data)
            .then((response) => {
                const firstInvoice = response.data
                const allInvoices = this.props.invoices
                allInvoices.push(firstInvoice)
                this.setState({
                    success: true,
                    showSuccessMessage: true,
                    showErrorMessage: false
                })
                this.props.action(allInvoices)
                localStorage.removeItem('invoiceForm')
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors,
                    showErrorMessage: true,
                    showSuccessMessage: false
                })

                console.warn(error)
            })
    }

    createInvoice (data) {
        const url = parseInt(this.state.finance_type) === 2 ? '/api/quote' : '/api/invoice'

        axios.post(url, data)
            .then((response) => {
                const firstInvoice = response.data
                const allInvoices = this.props.invoices
                allInvoices.push(firstInvoice)
                this.props.action(allInvoices)
                localStorage.removeItem('invoiceForm')
                this.setState({
                    showSuccessMessage: true,
                    showErrorMessage: false
                })
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors,
                    showErrorMessage: true,
                    showSuccessMessage: false
                })

                console.warn(error)
            })
    }

    handleApprove () {
        axios.get(`/api/quotes/approve/${this.state.invoice_id}`)
            .then((r) => {
                this.setState({
                    showSuccessMessage: true,
                    showErrorMessage: false
                })
            })
            .catch((e) => {
                this.setState({
                    showErrorMessage: true,
                    showSuccessMessage: false
                })
                console.warn(e)
            })
    }

    setRecurring (recurring) {
        this.setState({ recurring: recurring })
    }

    handleContactChange (e) {
        const invitations = this.state.invitations

        const contact = e.target.value

        // check if the check box is checked or unchecked
        if (e.target.checked) {
            alert(contact)
            // add the numerical value of the checkbox to options array
            invitations.push({ client_contact_id: contact })
        } else {
            // or remove the value from the unchecked checkbox from the array
            const index = invitations.findIndex(contact => contact.client_contact_id === contact)
            invitations.splice(index, 1)
        }

        // update the state with the new array of options
        this.setState({ invitations: invitations }, () => localStorage.setItem('invoiceForm', JSON.stringify(this.state)))
    }

    buildForm () {
        const changeStatusButton = this.state.status_id === 1
            ? <DropdownItem onClick={() => this.changeStatus('mark_sent')}>Mark Sent</DropdownItem>
            : <DropdownItem color="primary" onClick={() => this.changeStatus('mark_paid')}>Mark Paid</DropdownItem>

        const approveButton = this.state.status_id !== 4
            ? <DropdownItem className="primary" onClick={this.handleApprove}>Approve</DropdownItem> : null

        const sendEmailButton = <DropdownItem className="primary" onClick={() => this.changeStatus('email')}>Send
            Email</DropdownItem>

        const downloadButton = <DropdownItem className="primary"
            onClick={() => this.changeStatus('download')}>Download</DropdownItem>

        const deleteButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('delete')}>Delete</DropdownItem> : null

        const archiveButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('archive')}>Archive</DropdownItem> : null

        const cloneToQuote = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_quote')}>Clone To
                Quote</DropdownItem> : null

        const cloneButton =
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_invoice')}>Clone</DropdownItem>

        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        const dropdownMenu = this.state.invoice_id
            ? <Dropdown isOpen={this.state.dropdownOpen} toggle={this.toggleMenu}>
                <DropdownToggle caret>
                    Actions
                </DropdownToggle>

                <DropdownMenu>
                    <DropdownItem header>Header</DropdownItem>
                    {changeStatusButton}
                    {sendEmailButton}
                    {downloadButton}
                    {deleteButton}
                    {archiveButton}
                    {cloneToQuote}
                    {cloneButton}
                    {this.props.task_id ? <DropdownItem className="primary" onClick={this.handleTaskChange}>Get
                        Products</DropdownItem> : null}
                </DropdownMenu>
            </Dropdown> : null

        const successMessage = this.state.showSuccessMessage === true
            ? <SuccessMessage message="Invoice was updated successfully"/> : null
        const errorMessage = this.state.showErrorMessage === true
            ? <ErrorMessage message="Something went wrong"/> : null

        const documentTabLink = this.state.invoice_id !== null ? <NavItem>
            <NavLink
                className={this.state.activeTab === '5' ? 'active' : ''}
                onClick={() => {
                    this.toggleTab('5')
                }}>
                Documents
            </NavLink>
        </NavItem> : null

        const documentTab = this.state.invoice_id !== null
            ? <TabPane tabId="5">
                <Card>
                    <CardHeader>Documents</CardHeader>
                    <CardBody>
                        <FileUploads entity_type="App\Invoice" entity={this.props.invoice}
                            user_id={this.props.invoice.user_id}/>
                    </CardBody>
                </Card>
            </TabPane> : null

        const detailsForm = <React.Fragment>

            <Card>
                <CardHeader>Recurring</CardHeader>
                <CardBody>
                    <FormGroup>
                        <Label>Is Recurring?</Label>
                        <Input type="checkbox" onChange={this.handleSlideClick}/>
                    </FormGroup>

                    <div className={this.state.is_recurring ? 'collapse show' : 'collapse'}>
                        <AddRecurringInvoice
                            finance_type={this.props.finance_type}
                            invoice={this.props.invoice}
                            setRecurring={this.setRecurring}
                        />

                    </div>
                </CardBody>
            </Card>

            <Card>
                <CardHeader>Details</CardHeader>
                <CardBody>

                    <h2>{this.state.customerName}</h2>
                    <Address address={this.state.address}/>

                    <FormGroup>
                        <Label for="date">Invoice Date(*):</Label>
                        <Input value={this.state.date} type="date" id="date" name="date"
                            onChange={this.handleInput}/>
                        {this.renderErrorFor('due_date')}
                    </FormGroup>
                    <FormGroup>
                        <Label for="due_date">Due Date(*):</Label>
                        <Input value={this.state.due_date} type="date" id="due_date" name="due_date"
                            onChange={this.handleInput}/>
                        {this.renderErrorFor('due_date')}
                    </FormGroup>
                    <FormGroup>
                        <Label for="po_number">PO Number(*):</Label>
                        <Input value={this.state.po_number} type="text" id="po_number" name="po_number"
                            onChange={this.handleInput}/>
                        {this.renderErrorFor('po_number')}
                    </FormGroup>
                    <FormGroup>
                        <Label>Partial</Label>
                        <Input
                            value={this.state.partial}
                            type='text'
                            name='partial'
                            id='partial'
                            onChange={this.handleInput}
                        />
                    </FormGroup>

                    <CustomerDropdown
                        handleInputChanges={this.handleInput}
                        customer={this.state.customer_id}
                        customers={this.state.customers}
                        errors={this.state.errors}
                    />

                    <CompanyDropdown
                        company_id={this.state.company_id}
                        name="company_id"
                        hasErrorFor={this.hasErrorFor}
                        errors={this.state.errors}
                        handleInputChanges={this.handleInput}
                    />

                    {customForm}
                </CardBody>
            </Card>
        </React.Fragment>

        const contactsForm = <Card>
            <CardHeader>Invitations</CardHeader>
            <CardBody>
                {this.state.contacts.length && this.state.contacts.map(contact => {
                    const invitations = this.state.invitations.length ? this.state.invitations.filter(invitation => parseInt(invitation.client_contact_id) === contact.id) : []
                    const checked = invitations.length ? 'checked="checked"' : ''
                    return <FormGroup check>
                        <Label check>
                            <Input checked={checked} value={contact.id} onChange={this.handleContactChange}
                                type="checkbox"/> {`${contact.first_name} ${contact.last_name}`}
                        </Label>
                    </FormGroup>
                })
                }

                {!this.state.contacts.length &&
                <h2>You haven't selected a customer</h2>
                }
            </CardBody>
        </Card>

        const taxesTab =
            <Card>
                <CardHeader>Items</CardHeader>
                <CardBody>
                    <FormGroup>
                        <Label>Tax</Label>
                        <TaxRateDropdown
                            name="tax"
                            handleInputChanges={this.handleInput}
                            errors={this.state.errors}
                        />
                    </FormGroup>

                    <FormGroup>
                        <Label>Discount</Label>
                        <Input
                            value={this.state.discount}
                            type='text'
                            name='discount'
                            id='discount'
                            onChange={this.handleInput}
                        />
                    </FormGroup>
                </CardBody>
            </Card>

        const itemsForm = <Card>
            <CardHeader>Items</CardHeader>
            <CardBody>
                <LineItemEditor
                    finance_type={this.state.finance_type}
                    total={this.state.total}
                    sub_total={this.state.sub_total}
                    tax_total={this.state.tax_total}
                    discount_total={this.state.discount_total}
                    rows={this.state.data}
                    delete={this.handleDelete}
                    update={this.handleFieldChange}
                    onAddFiled={this.handleAddFiled}
                    setTotal={this.setTotal}/>

                <br/>
                <br/>
            </CardBody>
        </Card>

        const notesForm = <Card>
            <CardHeader>Notes</CardHeader>
            <CardBody>
                <FormGroup className="mb-2 mr-sm-2 mb-sm-0">
                    <Label>Private Notes</Label>
                    <Input
                        value={this.state.private_notes}
                        type='textarea'
                        name='private_notes'
                        id='private_notes'
                        onChange={this.handleInput}
                    />
                </FormGroup>

                <FormGroup className="mb-2 mr-sm-2 mb-sm-0">
                    <Label>Public Notes</Label>
                    <Input
                        value={this.state.public_notes}
                        type='textarea'
                        name='public_notes'
                        id='public_notes'
                        onChange={this.handleInput}
                    />
                </FormGroup>

                <FormGroup className="mb-2 mr-sm-2 mb-sm-0">
                    <Label>Terms</Label>
                    <Input
                        value={this.state.terms}
                        type='textarea'
                        name='terms'
                        id='notes'
                        onChange={this.handleInput}
                    />
                </FormGroup>

                <FormGroup className="mb-2 mr-sm-2 mb-sm-0">
                    <Label>Footer</Label>
                    <Input
                        value={this.state.footer}
                        type='textarea'
                        name='footer'
                        id='footer'
                        onChange={this.handleInput}
                    />

                </FormGroup>

            </CardBody>
        </Card>

        const isMobile = this.state.width <= 500
        const form = isMobile
            ? <React.Fragment>
                <Nav tabs>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('1')
                            }}>
                            Details
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('2')
                            }}>
                            Contacts
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '3' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('3')
                            }}>
                            Items
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '4' ? 'active' : ''}
                            onClick={() => {
                                this.toggleTab('4')
                            }}>
                            Notes
                        </NavLink>
                    </NavItem>
                    {documentTabLink}
                </Nav>

                <TabContent activeTab={this.state.activeTab}>
                    <TabPane tabId="1">
                        {detailsForm}
                    </TabPane>

                    <TabPane tabId="2">
                        {contactsForm}
                    </TabPane>

                    <TabPane tabId="3">
                        {taxesTab}
                        {itemsForm}
                    </TabPane>

                    <TabPane tabId="4">
                        {notesForm}
                    </TabPane>

                    {documentTab}
                </TabContent>
            </React.Fragment>

            : <React.Fragment>
                <Row form>
                    <Col md={6}>
                        {detailsForm}
                    </Col>

                    <Col md={6}>
                        {contactsForm}
                        {taxesTab}
                    </Col>
                </Row>
                {itemsForm}

                <Row form>
                    <Col md={6}>
                        {notesForm}
                    </Col>

                    <Col md={6}>
                        {documentTab}
                    </Col>
                </Row>
            </React.Fragment>

        return (
            <div>
                {dropdownMenu}
                {successMessage}
                {errorMessage}
                {form}

            </div>
        )
    }

    render () {
        const form = this.buildForm()
        const { success } = this.state
        const button = this.props.add === true ? <AddButtons toggle={this.toggle}/>
            : <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>

        if (this.props.modal) {
            return (
                <React.Fragment>
                    {button}
                    <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className} size="lg">
                        <ModalHeader toggle={this.toggle}>
                            Invoice
                        </ModalHeader>

                        <ModalBody>
                            {form}
                        </ModalBody>
                        <ModalFooter>
                            <Button color="success" onClick={this.saveData}>Save</Button>
                            <Button color="secondary" onClick={this.toggle}>Close</Button>
                        </ModalFooter>
                    </Modal>
                </React.Fragment>
            )
        }

        return (
            <div>

                {success && <div className="alert alert-success" role="alert">
                    Products added to task successfully
                </div>}

                {form}
                <Button color="success" onClick={this.saveData}>Save</Button>
            </div>
        )
    }
}

export default EditInvoice
