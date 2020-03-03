import React, { Component } from 'react'
import {
    Button,
    Card,
    CardHeader,
    CardBody,
    NavLink,
    Form,
    NavItem,
    Nav,
    TabPane,
    TabContent,
    FormGroup,
    Input,
    Label
} from 'reactstrap'
import axios from 'axios'
import { ToastContainer, toast } from 'react-toastify'

class CustomFieldSettings extends Component {
    constructor (props) {
        super(props)

        this.state = {
            activeTab: '1',
            quotes: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }],
            companies: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }],
            customers: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }],
            product: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }],
            invoices: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }],
            payments: [{ name: 'custom_value1', label: '' }, { name: 'custom_value2', label: '' }, {
                name: 'custom_value3',
                label: ''
            }, { name: 'custom_value4', label: '' }]
        }

        this.handleChange = this.handleChange.bind(this)
        this.handleSubmit = this.handleSubmit.bind(this)
        this.getSettings = this.getSettings.bind(this)
    }

    componentDidMount () {
        this.getSettings()
    }

    getSettings () {
        axios.get('api/accounts/fields/getAllCustomFields')
            .then((r) => {
                if (r.data.Customer && Object.keys(r.data)) {
                    this.setState({
                        product: r.data.Product,
                        customers: r.data.Customer,
                        payments: r.data.Payment,
                        invoices: r.data.Invoice,
                        companies: r.data.Company,
                        quotes: r.data.Quote
                    })
                    console.log('response', r.data.Product)
                }
            })
            .catch((e) => {
                toast.error('There was an issue updating the settings')
            })
    }

    handleChange (e) {
        const entity = e.target.dataset.entity
        const id = e.target.dataset.id
        const className = e.target.dataset.field
        const value = e.target.value

        if (['type', 'label'].includes(className)) {
            const products = [...this.state[entity]]
            products[id][className] = value
            this.setState({ [entity]: products }, () => console.log(this.state))
        } else {
            // this.setState({ [e.target.name]: e.target.value })
        }
    }

    handleSubmit (e) {
        const fields = {}
        fields.Product = this.state.product
        fields.Customer = this.state.customers
        fields.Company = this.state.companies
        fields.Payment = this.state.payments
        fields.Invoice = this.state.invoices
        fields.Quote = this.state.quotes

        axios.post('/api/accounts/fields', {
            fields: JSON.stringify(fields)
        }).then((response) => {
            toast.success('Settings updated successfully')
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

    toggle (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    render () {
        const { customers, product, invoices, payments, companies, quotes } = this.state

        const tabContent = customers ? <TabContent activeTab={this.state.activeTab}>
            <TabPane tabId="1">
                <Card>
                    <CardHeader>Customers</CardHeader>
                    <CardBody>
                        {
                            customers.map((val, idx) => {
                                const catId = `custom_value${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <Label htmlFor={catId}>{`Custom Field #${idx + 1}`}</Label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-id={idx}
                                                data-entity="customers"
                                                id={catId}
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={customers[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>

                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="customers"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={customers[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>
            <TabPane tabId="2">
                <Card>
                    <CardHeader>Companies</CardHeader>
                    <CardBody>
                        {
                            companies.map((val, idx) => {
                                const catId = `custom_value${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <Label htmlFor={catId}>{`Custom Field #${idx + 1}`}</Label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-entity="companies"
                                                data-id={idx}
                                                id={catId}
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={companies[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>
                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="companies"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={companies[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>
            <TabPane tabId="3">
                <Card>
                    <CardHeader>Products</CardHeader>
                    <CardBody>
                        {
                            product.map((val, idx) => {
                                const catId = `custom_value${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <Label htmlFor={catId}>{`Custom Field #${idx + 1}`}</Label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-id={idx}
                                                data-entity="product"
                                                id={catId}
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={product[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>
                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="product"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={product[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>

            <TabPane tabId="4">
                <Card>
                    <CardHeader>Invoice</CardHeader>
                    <CardBody>
                        {
                            invoices.map((val, idx) => {
                                const catId = `custom_name${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <Label htmlFor={catId}>{`Custom Field #${idx + 1}`}</Label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-id={idx}
                                                id={catId}
                                                data-entity="invoices"
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={invoices[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>
                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="invoices"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={invoices[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>

            <TabPane tabId="5">
                <Card>
                    <CardHeader>Payments</CardHeader>
                    <CardBody>
                        {
                            payments.map((val, idx) => {
                                const catId = `custom_value${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <label htmlFor={catId}>{`Custom Field #${idx + 1}`}</label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-id={idx}
                                                data-entity="payments"
                                                id={catId}
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={payments[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>
                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="payments"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={payments[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>

            <TabPane tabId="6">
                <Card>
                    <CardHeader>Quotes</CardHeader>
                    <CardBody>
                        {
                            quotes.map((val, idx) => {
                                const catId = `custom_value${idx}`
                                const ageId = `age-${idx}`
                                return (
                                    <Form key={idx} inline>
                                        <FormGroup className="mb-4" key={idx}>
                                            <label htmlFor={catId}>{`Custom Field #${idx + 1}`}</label>
                                            <Input
                                                type="select"
                                                name={catId}
                                                data-id={idx}
                                                data-entity="quotes"
                                                id={catId}
                                                data-field="type"
                                                onChange={this.handleChange}
                                                value={quotes[idx].type}
                                            >
                                                <option value='text'>Text</option>
                                                <option value='textarea'>Textarea</option>
                                                <option value='select'>Select List</option>
                                                <option value='checkbox'>Switch</option>
                                            </Input>
                                            <Label htmlFor={ageId}>Label</Label>
                                            <Input
                                                type="text"
                                                name={ageId}
                                                data-id={idx}
                                                data-entity="quotes"
                                                id={ageId}
                                                data-field="label"
                                                onChange={this.handleChange}
                                                value={quotes[idx].label}
                                            />
                                        </FormGroup>
                                    </Form>
                                )
                            })
                        }
                    </CardBody>
                </Card>
            </TabPane>

            <Button color="primary" onClick={this.handleSubmit}>Save</Button>
        </TabContent> : null

        return customers ? (
            <React.Fragment>
                <ToastContainer/>

                <Nav tabs>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('1')
                            }}>
                            Customers
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('2')
                            }}>
                            Companies
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '3' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('3')
                            }}>
                            Products
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '4' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('4')
                            }}>
                            Invoices
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '5' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('5')
                            }}>
                            Payments
                        </NavLink>
                    </NavItem>

                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '6' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('6')
                            }}>
                            Quotes
                        </NavLink>
                    </NavItem>
                </Nav>
                {tabContent}
            </React.Fragment>
        ) : null
    }
}

export default CustomFieldSettings
