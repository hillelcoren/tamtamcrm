import React, { Component } from 'react'
import FormBuilder from './FormBuilder'
import {
    Button,
    CustomInput,
    FormGroup,
    Label,
    Card,
    CardHeader,
    CardBody,
    Nav,
    NavItem,
    NavLink,
    TabContent,
    TabPane
} from 'reactstrap'
import axios from 'axios'
import { ToastContainer, toast } from 'react-toastify'

class Settings extends Component {
    constructor (props) {
        super(props)
        
        this.state = {
            id: this.props.match.params.add && this.props.match.params.add === 'true' ? null : localStorage.getItem('account_id'),
            loaded: false,
            settings: {},
            company_logo: null,
            activeTab: '1'
        }

        this.handleSettingsChange = this.handleSettingsChange.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleSubmit = this.handleSubmit.bind(this)
        this.getAccount = this.getAccount.bind(this)
        this.toggle = this.toggle.bind(this)
    }

    componentDidMount () {
        this.getAccount()
    }

    toggle (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    getAccount () {
        if (this.state.id === null) {
            this.setState({ loaded: true })
            return
        }

        axios.get(`api/accounts/${this.state.id}`)
            .then((r) => {
                this.setState({
                    loaded: true,
                    settings: r.data.settings
                })
            })
            .catch((e) => {
                toast.error('There was an issue updating the settings')
            })
    }

    handleChange (event) {
        this.setState({ [event.target.name]: event.target.value })
    }

    handleSettingsChange (event) {
        const name = event.target.name
        const value = event.target.value

        this.setState(prevState => ({
            settings: {
                ...prevState.settings,
                [name]: value
            }
        }))
    }

    handleFileChange (e) {
        this.setState({
            [e.target.name]: e.target.files[0]
        })
    }

    handleSubmit (e) {
        const url = this.state.id === null ? '/api/accounts' : `/api/accounts/${this.state.id}`

        const formData = new FormData()
        formData.append('settings', JSON.stringify(this.state.settings))
        formData.append('company_logo', this.state.company_logo)

        if (this.state.id !== null) {
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

    getAddressFields () {
        const settings = this.state.settings

        const formFields = [
            [
                {
                    name: 'address1',
                    label: 'Address 1',
                    type: 'text',
                    placeholder: 'Address 1',
                    value: settings.address1,
                    group: 2
                },
                {
                    name: 'address2',
                    label: 'Address 2',
                    type: 'text',
                    placeholder: 'Address 2',
                    value: settings.address2,
                    group: 2
                },
                {
                    name: 'city',
                    label: 'City',
                    type: 'text',
                    placeholder: 'City',
                    value: settings.city,
                    group: 2
                },
                {
                    name: 'state',
                    label: 'State',
                    type: 'text',
                    placeholder: 'State',
                    value: settings.state,
                    group: 2
                },
                {
                    name: 'postal_code',
                    label: 'Postal Code',
                    type: 'text',
                    placeholder: 'Postal Code',
                    value: settings.postal_code,
                    group: 2
                },
                {
                    name: 'country_id',
                    label: 'Country',
                    type: 'country',
                    placeholder: 'Country',
                    value: settings.country_id,
                    group: 2
                }
            ]
        ]

        return formFields
    }

    getFormFields () {
        const settings = this.state.settings

        const formFields = [
            [
                {
                    name: 'name',
                    label: 'Name',
                    type: 'text',
                    placeholder: 'Name',
                    value: settings.name,
                    group: 1
                },
                {
                    name: 'website',
                    label: 'Website',
                    type: 'text',
                    placeholder: 'Website',
                    value: settings.website,
                    group: 1
                },
                {
                    name: 'phone',
                    label: 'Phone Number',
                    type: 'text',
                    placeholder: 'Phone Number',
                    value: settings.phone,
                    group: 1
                },
                {
                    name: 'email',
                    label: 'Email',
                    type: 'text',
                    placeholder: 'Email',
                    value: settings.email,
                    group: 1
                },
                {
                    name: 'vat_number',
                    label: 'VAT Number',
                    type: 'text',
                    placeholder: 'VAT Number',
                    value: settings.vat_number,
                    group: 1
                },

                {
                    name: 'currency_id',
                    label: 'Currency',
                    type: 'currency',
                    placeholder: 'Currency',
                    value: settings.currency_id,
                    group: 3
                },
                {
                    name: 'email_style',
                    label: 'Email Template',
                    type: 'select',
                    value: settings.design,
                    group: 3,
                    options: [
                        {
                            value: 'plain',
                            text: 'Plain'
                        },
                        {
                            value: 'light',
                            text: 'Light'
                        },
                        {
                            value: 'dark',
                            text: 'Dark'
                        },
                        {
                            value: 'custom',
                            text: 'Custom'
                        }
                    ]
                }
            ]
        ]

        return formFields
    }

    render () {
        return this.state.loaded === true ? (
            <React.Fragment>
                <ToastContainer/>

                <Nav tabs>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('1')
                            }}>
                            Details
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('2')
                            }}>
                            Address
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '3' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('3')
                            }}>
                            Logo
                        </NavLink>
                    </NavItem>
                </Nav>
                <TabContent activeTab={this.state.activeTab}>
                    <TabPane tabId="1">
                        <Card>
                            <CardHeader>Details</CardHeader>
                            <CardBody>
                                <FormBuilder
                                    handleChange={this.handleSettingsChange}
                                    formFieldsRows={this.getFormFields()}
                                />
                            </CardBody>
                        </Card>
                    </TabPane>
                    <TabPane tabId="2">
                        <Card>
                            <CardHeader>Address</CardHeader>
                            <CardBody>
                                <FormBuilder
                                    handleChange={this.handleSettingsChange}
                                    formFieldsRows={this.getAddressFields()}
                                />
                            </CardBody>
                        </Card>
                    </TabPane>
                    <TabPane tabId="3">
                        <Card>
                            <CardHeader>Logo</CardHeader>
                            <CardBody>
                                <FormGroup>

                                    <Label>Logo</Label>
                                    <CustomInput className="mt-4 mb-4" onChange={this.handleFileChange.bind(this)}
                                        type="file"
                                        id="company_logo" name="company_logo"
                                        label="Logo"/>
                                </FormGroup>
                            </CardBody>
                        </Card>
                    </TabPane>
                    <Button color="primary" onClick={this.handleSubmit}>Save</Button>
                </TabContent>

            </React.Fragment>
        ) : null
    }
}

export default Settings
