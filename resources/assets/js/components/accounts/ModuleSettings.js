import React, { Component } from 'react'
import {
    Form,
    FormGroup,
    Label,
    CustomInput
} from 'reactstrap'

class ModuleSettings extends Component {
    constructor (props) {
        super(props)
        this.state = {
            modules: [
                {
                    id: 1,
                    value: 1,
                    label: 'Recurring Invoices',
                    isChecked: false
                },
                {
                    id: 2,
                    value: 2,
                    label: 'Credits',
                    isChecked: false
                },
                {
                    id: 3,
                    value: 4,
                    label: 'Quotes',
                    isChecked: false
                },
                { id: 4, value: 8, label: 'Tasks', isChecked: false },
                {
                    id: 4,
                    value: 16,
                    label: 'Expenses',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 32,
                    label: 'Projects',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 64,
                    label: 'Vendors',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 128,
                    label: 'Cases',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 512,
                    label: 'Recurring Expenses',
                    isChecked: false
                },
                {
                    id: 4,
                    value: 1024,
                    label: 'Recurring Tasks',
                    isChecked: false
                }
            ]
        }

        this.customInputSwitched = this.customInputSwitched.bind(this)
        this.handleAllChecked = this.handleAllChecked.bind(this)
    }

    handleAllChecked (event) {
        const modules = this.state.modules
        modules.forEach(module => module.isChecked = event.target.checked)
        this.setState({ modules: modules })
    }

    customInputSwitched (buttonName, e) {
        const checked = e.target.checked
        const modules = this.state.modules

        modules.forEach(module => {
            if (module.value === buttonName) {
                module.isChecked = checked
            }
        })
        this.setState({ modules: modules }, () => {
            console.log('test', this.state.modules)
        })
    }

    render () {
        return (
            <div>
                <p>Start editing to see some magic happen :)</p>
                <Form>
                    <FormGroup>
                        <Label for="exampleCheckbox">Switches <input type="checkbox" onClick={this.handleAllChecked} />Check all </Label>
                        {this.state.modules.map((module, index) => {
                            // console.log(disease, index);
                            const idName = 'exampleCustomSwitch' + index

                            return (
                                <div key={index}>
                                    <CustomInput
                                        checked={module.isChecked}
                                        type="switch"
                                        id={idName}
                                        name="customSwitch"
                                        label={module.label}
                                        onChange={this.customInputSwitched.bind(this, module.value)}
                                    />
                                </div>
                            )
                        }
                        )}
                    </FormGroup>
                </Form>
                {this.state.log}
            </div>
        )
    }
}

export default ModuleSettings
