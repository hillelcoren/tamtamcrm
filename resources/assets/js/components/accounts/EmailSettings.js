import React, { Component } from 'react'
import FormBuilder from './FormBuilder'
import { Button, Card, CardBody, CardHeader } from 'reactstrap'
import axios from 'axios'
import { toast, ToastContainer } from 'react-toastify'
import SignatureCanvas from 'react-signature-canvas'
import styles from './style.module.css'

class EmailSettings extends Component {
    constructor (props) {
        super(props)

        this.state = {
            id: localStorage.getItem('account_id'),
            sigPad: {},
            settings: {}
        }

        this.handleSettingsChange = this.handleSettingsChange.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleSubmit = this.handleSubmit.bind(this)
        this.getAccount = this.getAccount.bind(this)
        this.trim = this.trim.bind(this)
    }

    componentDidMount () {
        this.getAccount()
    }

    getAccount () {
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

    trim () {
        const value = this.state.sigPad.getTrimmedCanvas()
            .toDataURL('image/png')

        return new Promise((resolve, reject) => {
            this.setState(prevState => ({
                settings: {
                    ...prevState.settings,
                    email_signature: value
                }
            }), () => resolve(true))
        })
    }

    handleSubmit (e) {
        this.trim().then(result => {
            axios.put(`/api/accounts/${this.state.id}`, { settings: JSON.stringify(this.state.settings) }, {
            }).then((response) => {
                toast.success('Settings updated successfully')
            }).catch((error) => {
                toast.error(`There was an issue updating the settings ${error}`)
            })
        })
    }

    getFormFields () {
        const settings = this.state.settings

        const formFields = [
            [
                {
                    name: 'reply_to_email',
                    label: 'Reply To Email',
                    type: 'text',
                    placeholder: 'Reply To Email',
                    value: settings.reply_to_email
                },
                {
                    name: 'bcc_email',
                    label: 'BCC Email',
                    type: 'text',
                    placeholder: 'BCC Email',
                    value: settings.bcc_email
                },
                {
                    name: 'pdf_email_attachment',
                    label: 'Attach PDF',
                    type: 'select',
                    placeholder: 'Attach PDF',
                    value: settings.pdf_email_attachment,
                    options: [
                        {
                            value: '1',
                            text: 'Yes'
                        },
                        {
                            value: '0',
                            text: 'No'
                        }
                    ]
                },
                {
                    name: 'ubl_email_attachment',
                    label: 'Attach UBL',
                    type: 'select',
                    placeholder: 'Attach UBL',
                    value: settings.ubl_email_attachment,
                    options: [
                        {
                            value: '1',
                            text: 'Yes'
                        },
                        {
                            value: '0',
                            text: 'No'
                        }
                    ]
                },
                {
                    name: 'enable_email_markup',
                    label: 'Enable Markup',
                    type: 'select',
                    placeholder: 'Enable Markup',
                    value: settings.enable_email_markup,
                    options: [
                        {
                            value: '1',
                            text: 'Yes'
                        },
                        {
                            value: '0',
                            text: 'No'
                        }
                    ]
                },
                {
                    name: 'email_style',
                    label: 'Email Style',
                    type: 'select',
                    placeholder: 'Email Style',
                    value: settings.email_style,
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
                <Card>
                    <CardHeader>Settings</CardHeader>
                    <CardBody>
                        <FormBuilder
                            handleChange={this.handleSettingsChange}
                            formFieldsRows={this.getFormFields()}
                        />

                        <SignatureCanvas canvasProps={{ className: styles.sigPad }}
                            ref={(ref) => { this.state.sigPad = ref }} />

                        <Button color="primary" onClick={this.handleSubmit}>Save</Button>
                    </CardBody>
                </Card>
            </React.Fragment>
        ) : null
    }
}

export default EmailSettings
