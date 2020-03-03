import React from 'react'
import { FormGroup, Label, Input, Card, CardBody, CardHeader } from 'reactstrap'
import PaymentTypeDropdown from '../common/PaymentTypeDropdown'
import CompanyDropdown from '../common/CompanyDropdown'
import CurrencyDropdown from '../common/CurrencyDropdown'
import UserDropdown from '../common/UserDropdown'
import GroupSettingsDropdown from '../common/GroupSettingsDropdown'

export default function SettingsForm (props) {
    const hasErrorFor = (field) => {
        return props.errors && !!props.errors[field]
    }

    const renderErrorFor = (field) => {
        if (hasErrorFor(field)) {
            return (
                <span className='invalid-feedback'>
                    <strong>{props.errors[field][0]}</strong>
                </span>
            )
        }
    }

    return (
        <Card>
            <CardHeader>Additional Info</CardHeader>
            <CardBody>

                <FormGroup>
                    <Label htmlFor="payment_terms"> Payment Terms </Label>
                    <Input className={hasErrorFor('payment_terms') ? 'is-invalid' : ''} type="text"
                        id="payment_terms"
                        data-namespace="settings"
                        value={props.settings.payment_terms}
                        onChange={props.onChange} name="payment_terms"
                        placeholder="Enter days"/>
                    {renderErrorFor('payment_terms')}
                </FormGroup>

                <PaymentTypeDropdown
                    name="default_payment_method"
                    data-namespace="customer"
                    payment_type={props.customer.default_payment_method}
                    errors={props.errors}
                    handleInputChanges={props.onChange}
                />

                <CompanyDropdown
                    data-namespace="customer"
                    company_id={props.customer.company_id}
                    errors={props.errors}
                    handleInputChanges={props.onChange}
                />

                <CurrencyDropdown
                    data-namespace="customer"
                    currency_id={props.customer.currency_id}
                    errors={props.errors}
                    handleInputChanges={props.onChange}
                />

                <UserDropdown
                    data-namespace="customer"
                    user_id={props.customer.assigned_user}
                    name="assigned_user"
                    errors={props.errors}
                    handleInputChanges={props.onChange}
                />

                <GroupSettingsDropdown
                    data-namespace="customer"
                    group_settings_id={props.customer.group_settings_id}
                    name="group_settings_id"
                    errors={props.errors}
                    handleInputChanges={props.onChange}
                />
            </CardBody>
        </Card>

    )
}
