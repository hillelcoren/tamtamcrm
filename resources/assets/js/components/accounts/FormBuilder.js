import React from 'react'
import { Input, FormGroup, Label } from 'reactstrap'
import CountryDropdown from '../common/CountryDropdown'
import CurrencyDropdown from '../common/CurrencyDropdown'
import Switch from '../common/Switch'

/**
 * A component which renders a form based on a given list of fields.
 */
class FormBuilder extends React.Component {
    constructor (props) {
        super(props)

        const formFields = this.getFormFields()

        // dynamically construct our initial state by using
        // each form field's name as an object property.
        const formFieldNames = formFields.reduce((obj, field) => {
            obj[field.name] = ''
            return obj
        }, {})

        // define the initial state, so we can use it later on
        // when we'll need to reset the form
        this.initialState = {
            ...formFieldNames
        }

        this.state = this.initialState
    }

    getFormFields () {
        const { formFieldsRows } = this.props
        const formFields = []

        formFieldsRows.forEach((formFieldsRow) => {
            formFields.push(...formFieldsRow)
        })

        return formFields
    }

    buildSelectList (field) {
        const arrayOfData = field.options
        const options = arrayOfData.map((data) =>
            <option
                key={data.value}
                value={data.value}
            >
                {data.text}
            </option>
        )

        return (
            <FormGroup>
                <Label>{field.label}</Label>
                <Input value={field.value} type="select" name={field.name} onChange={this.props.handleChange}>
                    <option>Select Item</option>
                    {options}
                </Input>
            </FormGroup>
        )
    }

    renderTextInput (field) {
        let returnedField = null

        if (field.name === '' || field.type === '' || field.label === '') {
            return
        }

        const id = field.id ? field.id : ''

        switch (field.type) {
            case 'currency':
                returnedField = <React.Fragment>
                    <FormGroup>
                        <Label>{field.label}</Label>
                        <CurrencyDropdown
                            currency_id={field.value}
                            errors={{}}
                            handleInputChanges={this.props.handleChange}
                        />
                    </FormGroup>
                </React.Fragment>
                break

            case 'country':
                returnedField = <React.Fragment>
                    <FormGroup>
                        <Label>{field.label}</Label>
                        <CountryDropdown
                            country={field.value}
                            errors={{}}
                            handleInputChanges={this.props.handleChange}
                        />
                    </FormGroup>
                </React.Fragment>
                break
            case 'checkbox':
                returnedField = <Switch
                    label={field.label}
                    name={field.name}
                    isOn={field.value}
                    handleToggle={this.props.handleCheckboxChange}
                />
                break

            case 'select':
                returnedField = this.buildSelectList(field)
                break

            default:
                returnedField = <React.Fragment>
                    <FormGroup>
                        <Label>{field.label}</Label>
                        <Input type={field.type}
                            id={id}
                            value={field.value}
                            name={field.name}
                            placeholder={field.placeholder}
                            onChange={this.props.handleChange}
                        />
                    </FormGroup>
                </React.Fragment>
                break
        }

        return returnedField
    }

    render () {
        const { formFieldsRows } = this.props

        return (
            <React.Fragment>
                {/* eslint-disable react/no-array-index-key */}
                {formFieldsRows.map((formFieldsRow, i) => (
                    <div key={`r-${i}`}>
                        {formFieldsRow.map(field => this.renderTextInput(field))}
                    </div>
                ))}
                {/* eslint-enable react/no-array-index-key */}
            </React.Fragment>
        )
    }
}

export default FormBuilder
