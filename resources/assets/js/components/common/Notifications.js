import React, { Component } from 'react'
import { CustomInput, Label } from 'reactstrap'

export default class Notifications extends Component {
    constructor (props) {
        super(props)
        this.state = {
            roles: [],
            modal: false,
            notifications: [
                {
                    id: 1,
                    value: 'payment_success',
                    label: 'Payment Successful',
                    isChecked: false
                },
                {
                    id: 1,
                    value: 'lead_success',
                    label: 'New Lead Created',
                    isChecked: false
                },
                {
                    id: 1,
                    value: 'deal_success',
                    label: 'New Deal Created',
                    isChecked: false
                },
                {
                    id: 2,
                    value: 'payment_failure',
                    label: 'Payment Failure',
                    isChecked: false
                },
                {
                    id: 3,
                    value: 'invoice_sent',
                    label: 'Invoice Sent',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'credit_sent',
                    label: 'Credit Sent',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'quote_sent',
                    label: 'Quote Sent',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'invoice_viewed',
                    label: 'Invoice Viewed',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'quote_viewed',
                    label: 'Quote Viewed',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'credit_viewed',
                    label: 'Credit Viewed',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 'quote_approved',
                    label: 'Quote Approved',
                    isChecked: false
                }
            ]
        }

        this.customInputSwitched = this.customInputSwitched.bind(this)
        this.handleAllChecked = this.handleAllChecked.bind(this)
    }

    componentDidMount () {
        if (this.props.notifications && Object.keys(this.props.notifications).length) {
            this.setState({ notifications: this.props.notifications })
        }
    }

    handleAllChecked (event) {
        const notifications = this.state.notifications
        notifications.forEach(notification => notification.isChecked = event.target.checked)
        this.setState({ notifications: notifications }, () => {
            this.props.onChange(this.state.notifications)
        })
    }

    customInputSwitched (buttonName, e) {
        const checked = e.target.checked
        const notifications = this.state.notifications

        notifications.forEach(notification => {
            if (notification.value === buttonName) {
                notification.isChecked = checked
            }
        })
        this.setState({ notifications: notifications }, () => {
            this.props.onChange(this.state.notifications)
        })
    }

    render () {
        return (<React.Fragment>
            <Label for="exampleCheckbox">Switches <input type="checkbox"
                onClick={this.handleAllChecked}/>Check
                all </Label>
            {this.state.notifications.map((notification, index) => {
                const idName = 'exampleCustomSwitch' + index

                return (
                    <div key={index}>
                        <CustomInput
                            checked={notification.isChecked}
                            type="switch"
                            id={idName}
                            name="customSwitch"
                            label={notification.label}
                            onChange={this.customInputSwitched.bind(this, notification.value)}
                        />
                    </div>
                )
            }
            )}
        </React.Fragment>)
    }
}
