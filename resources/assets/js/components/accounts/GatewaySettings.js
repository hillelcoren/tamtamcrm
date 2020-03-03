import React, { Component } from 'react'
import FormBuilder from './FormBuilder'
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
    Label,
    Row,
    Col
} from 'reactstrap'
import axios from 'axios'
import { ToastContainer, toast } from 'react-toastify'
import Checkbox from '../common/Checkbox'

class GatewaySettings extends Component {
    constructor (props) {
        super(props)

        this.state = {
            gateway_key: null,
            id: null,
            company_gateways: [],
            gateway_type_id: null,
            update_details: 1,
            show_billing_address: 1,
            show_shipping_address: 1,
            loaded: false,
            fees_and_limits: {},
            data: {
                fees_and_limits: [],
                config: [],
                settings: []
            },
            config: {},
            activeTab: '1',
            accepted_cards: new Map()
        }

        this.card_types = [
            {
                name: 'visa',
                label: 'Visa'
            },
            {
                name: 'mastercard',
                label: 'Mastercard'
            },
            {
                name: 'american_express',
                label: 'American Express'
            },
            {
                name: 'diners_card',
                label: 'Diners Card'
            },
            {
                name: 'discover_card',
                label: 'Discover Card'
            }
        ]

        this.handleSettingsChange = this.handleSettingsChange.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleConfigChange = this.handleConfigChange.bind(this)
        this.handleSubmit = this.handleSubmit.bind(this)
        this.getSettings = this.getSettings.bind(this)
        this.handleArrayChange = this.handleArrayChange.bind(this)
        this.handleCheckboxChange = this.handleCheckboxChange.bind(this)
        this.handleCardChange = this.handleCardChange.bind(this)
    }

    componentDidMount () {
        this.getSettings()
    }

    getSettings () {
        axios.get('api/company_gateways')
            .then((r) => {
                this.setState({
                    company_gateways: r.data,
                    loaded: true
                })
            })
            .catch((e) => {
                toast.error('There was an issue updating the settings')
            })
    }

    handleArrayChange (array) {
        const data = this.state.data
        data.settings[this.state.gateway_key] = { ...data.settings[this.state.gateway_key], ...array }
        console.log('array', data.settings)
        this.setState({ data: data })
    }

    handleChange (event) {
        const name = event.target.name
        const checked = name === 'gateway_key' ? event.target.value : event.target.checked

        const changeArray = name === 'gateway_key'

        this.setState({ [name]: checked }, () => {
            if (changeArray) {
                const data = this.state.data

                if (this.state.company_gateways[this.state.gateway_key] && Object.keys(this.state.company_gateways[this.state.gateway_key]).length) {
                    const companyGateway = this.state.company_gateways[this.state.gateway_key]

                    if (!data.fees_and_limits[this.state.gateway_key] || !Object.keys(data.fees_and_limits[this.state.gateway_key]).length) {
                        data.fees_and_limits[this.state.gateway_key] = this.state.company_gateways[this.state.gateway_key].fees_and_limits
                        data.config[this.state.gateway_key] = this.state.company_gateways[this.state.gateway_key].config

                        const settings =
                            {
                                require_cvv: companyGateway.require_cvv,
                                show_billing_address: companyGateway.show_billing_address,
                                show_shipping_address: companyGateway.show_billing_address,
                                update_details: companyGateway.update_details
                            }

                        data.settings[this.state.gateway_key] = settings
                        this.setState({ data: data, gateway_type_id: companyGateway.id })
                    }
                } else {
                    data.fees_and_limits[this.state.gateway_key] = []
                    data.config[this.state.gateway_key] = { apiKey: null, publishable_key: null }
                    data.settings[this.state.gateway_key] = {}
                    this.setState({ data: data, gateway_type_id: null })
                }
            } else {
                this.handleArrayChange({ [name]: checked })
            }
        })
    }

    handleSettingsChange (event) {
        const name = event.target.name
        const value = event.target.value

        this.setState(prevState => ({
            fees_and_limits: {
                ...prevState.fees_and_limits,
                [name]: value
            }
        }), function () {
            const data = this.state.data

            if (!data.fees_and_limits[this.state.gateway_key][0]) {
                data.fees_and_limits[this.state.gateway_key][0] = {}
            }

            data.fees_and_limits[this.state.gateway_key][0][name] = value
            console.log('state', data.fees_and_limits[this.state.gateway_key][0])
            this.setState({ data: data })
        })
    }

    handleConfigChange (event) {
        const name = event.target.name
        const value = event.target.value

        this.setState(prevState => ({
            config: {
                ...prevState.config,
                [name]: value
            }
        }), function () {
            const data = this.state.data

            console.log('config', data)

            data.config[this.state.gateway_key][name] = value
            this.setState({ data: data })
        })
    }

    handleSubmit (e) {
        const url = this.state.gateway_type_id ? `/api/company_gateways/${this.state.gateway_type_id}` : '/api/company_gateways'

        const formData = new FormData()
        formData.append('accepted_credit_cards', Array.from(this.state.accepted_cards.keys()).join(','))
        formData.append('fees_and_limits', JSON.stringify(this.state.data.fees_and_limits[this.state.gateway_key]))
        formData.append('config', JSON.stringify(this.state.data.config[this.state.gateway_key]))
        formData.append('gateway_type_id', this.state.gateway_type_id)
        formData.append('update_details', this.state.data.settings[this.state.gateway_key].update_details === true ? 1 : 0)
        formData.append('gateway_key', this.state.gateway_key)
        formData.append('show_billing_address', this.state.data.settings[this.state.gateway_key].show_billing_address === true ? 1 : 0)
        formData.append('show_shipping_address', this.state.data.settings[this.state.gateway_key].show_shipping_address === true ? 1 : 0)
        formData.append('require_cvv', this.state.data.settings[this.state.gateway_key].require_cvv === true ? 1 : 0)

        if (this.state.gateway_type_id) {
            formData.append('_method', 'PUT')
        }

        axios.post(url, formData, {
            headers: {
                'content-type': 'multipart/form-data'
            }
        })
            .then((response) => {
                toast.success('Settings updated successfully')
            })
            .catch((error) => {
                toast.error('There was an issue updating the settings')
            })
    }

    getConfigFields () {
        const settings = this.state.data.config[this.state.gateway_key] ? this.state.data.config[this.state.gateway_key] : ''

        const formFields = [
            [
                {
                    name: 'apiKey',
                    label: 'Api Key',
                    type: 'text',
                    placeholder: 'Api Key',
                    value: settings.apiKey ? settings.apiKey : '',
                    group: 1
                },
                {
                    name: 'publishable_key',
                    label: 'Publishable Key',
                    type: 'text',
                    placeholder: 'Publishable Key',
                    value: settings.publishable_key ? settings.publishable_key : '',
                    group: 1
                }
            ]
        ]

        return formFields
    }

    getMainFields () {
        const settings = this.state.data.settings[this.state.gateway_key] ? this.state.data.settings[this.state.gateway_key] : ''

        const formFields = [
            [
                {
                    name: 'require_cvv',
                    label: 'Require CVV',
                    type: 'checkbox',
                    placeholder: 'Require CVV',
                    value: settings.require_cvv ? settings.require_cvv : '',
                    group: 1
                },
                {
                    name: 'update_details',
                    label: 'Update Address',
                    type: 'checkbox',
                    placeholder: 'Update Address',
                    value: settings.update_details ? settings.update_details : '',
                    group: 1
                },
                {
                    name: 'show_billing_address',
                    label: 'Billing Address',
                    type: 'checkbox',
                    placeholder: 'Billing Address',
                    value: settings.show_billing_address ? settings.show_billing_address : '',
                    group: 1
                },

                {
                    name: 'show_shipping_address',
                    label: 'Shipping Address',
                    type: 'checkbox',
                    placeholder: 'Shipping Address',
                    value: settings.show_shipping_address ? settings.show_shipping_address : '',
                    group: 1
                }
            ]
        ]

        return formFields
    }

    toggle (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    getFormFields () {
        const settings = this.state.data.fees_and_limits[this.state.gateway_key] ? this.state.data.fees_and_limits[this.state.gateway_key][0] : ''

        const formFields = [
            [
                {
                    name: 'min_limit',
                    label: 'Min Limit',
                    type: 'text',
                    placeholder: 'Min Limit',
                    value: settings && settings.min_limit ? settings.min_limit : '',
                    group: 1
                },
                {
                    name: 'max_limit',
                    label: 'Max Limit',
                    type: 'text',
                    placeholder: 'Max Limit',
                    value: settings && settings.max_limit ? settings.max_limit : '',
                    group: 1
                },
                {
                    name: 'fee_amount',
                    label: 'Fee Amount',
                    type: 'text',
                    placeholder: 'Fee Amount',
                    value: settings && settings.fee_amount ? settings.fee_amount : '',
                    group: 1
                },
                {
                    name: 'fee_percent',
                    label: 'Fee Percent',
                    type: 'text',
                    placeholder: 'Fee Percent',
                    value: settings && settings.fee_percent ? settings.fee_percent : '',
                    group: 1
                },
                {
                    name: 'fee_cap',
                    label: 'Fee Cap',
                    type: 'text',
                    placeholder: 'Fee Cap',
                    value: settings && settings.fee_cap ? settings.fee_cap : '',
                    group: 2
                }
            ]
        ]

        return formFields
    }

    handleCardChange (e) {
        const item = e.target.name
        const isChecked = e.target.checked
        this.setState(prevState => ({ accepted_cards: prevState.accepted_cards.set(item, isChecked) }))
    }

    handleCheckboxChange (e) {
        const value = e.target.checked
        const name = e.target.name

        this.setState(prevState => ({
            settings: {
                ...prevState.settings,
                [name]: value
            }
        }))
    }

    render () {
        const tabContent = this.state.gateway_key ? <TabContent activeTab={this.state.activeTab}>
            <TabPane tabId="1">
                <Card>
                    <CardHeader>Credentials</CardHeader>
                    <CardBody>
                        <FormBuilder
                            handleChange={this.handleConfigChange}
                            formFieldsRows={this.getConfigFields()}
                        />
                    </CardBody>
                </Card>
            </TabPane>
            <TabPane tabId="2">
                <Form className="form-horizontal">
                    <Card>
                        <CardHeader>Settings</CardHeader>
                        <CardBody>
                            <FormBuilder
                                handleCheckboxChange={this.handleChange}
                                handleChange={this.handleChange}
                                formFieldsRows={this.getMainFields()}
                            />
                        </CardBody>
                    </Card>

                    <Card>
                        <CardHeader>Accepted Cards</CardHeader>
                        <CardBody>
                            <FormGroup>
                                <Row>
                                    <Col sm={10}>
                                        {
                                            this.card_types.map((item, index) => (
                                                <div key={index} className="form-check">
                                                    <Checkbox name={item.name}
                                                        checked={this.state.accepted_cards.get(item.name)}
                                                        onChange={this.handleCardChange}/>
                                                    <label className="form-check-label" htmlFor="gridRadios1">
                                                        {item.label}
                                                    </label>
                                                </div>
                                            ))
                                        }
                                    </Col>
                                </Row>
                            </FormGroup>

                        </CardBody>
                    </Card>
                </Form>
            </TabPane>
            <TabPane tabId="3">
                <Card>
                    <CardHeader>Limits \ Fees</CardHeader>
                    <CardBody>
                        <FormBuilder
                            handleChange={this.handleSettingsChange}
                            formFieldsRows={this.getFormFields()}
                        />
                    </CardBody>
                </Card>
            </TabPane>

            <Button color="primary" onClick={this.handleSubmit}>Save</Button>
        </TabContent> : null

        return this.state.loaded === true ? (
            <React.Fragment>
                <ToastContainer/>

                <FormGroup>
                    <Label>Gateway Type</Label>
                    <Input onChange={this.handleChange} type="select" name="gateway_key" value={this.state.gateway_key}>
                        <option value="">Select Gateway</option>
                        <option value="d14dd26a37cecc30fdd65700bfb55b23">Stripe</option>
                        <option value="c3dec814e14cbd7d86abd92ce6789f8c">Test Empty</option>
                    </Input>
                </FormGroup>

                <Nav tabs>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('1')
                            }}>
                            Credentials
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('2')
                            }}>
                            Settings
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '3' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('3')
                            }}>
                            Limits/Fees
                        </NavLink>
                    </NavItem>
                </Nav>
                {tabContent}
            </React.Fragment>
        ) : null
    }
}

export default GatewaySettings
