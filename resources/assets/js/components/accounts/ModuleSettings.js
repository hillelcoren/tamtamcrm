import React, { Component } from 'react'
import './App.css'
import  CheckBox  from './CheckBox'

class ModuleSettings extends Component {
  constructor(props) {
    super(props)
    this.state = {
      modules: [
        {id: 1, value: 1, label: "Recurring Invoices", isChecked: false},
        {id: 2, value: 2, label: "Credits", isChecked: false},
        {id: 3, value: 4, label: "Quotes", isChecked: false},
        {id: 4, value: 8, label: "Tasks", isChecked: false},
        {id: 4, value: 4, label: "Expenses", isChecked: false},
        {id: 4, value: 16, label: "Projects", isChecked: false},
        {id: 4, value: 64, label: "Vendors", isChecked: false},
        {id: 4, value: 128, label: "Cases", isChecked: false},
        {id: 4, value: 512, label: "Recurring Expenses", isChecked: false},
        {id: 4, value: 1024, label: "Recurring Tasks", isChecked: false}
      ]

      this.customInputSwitched.bind(this)
    }
  }
  
  handleAllChecked = (event) => {
    let modules = this.state.modules
    modules.forEach(module => module.isChecked = event.target.checked) 
    this.setState({modules: modules})
  }

  handleCheckChieldElement = (event) => {
    let modules = this.state.modules
    modules.forEach(module => {
       if (module.value === event.target.value)
          module.isChecked =  event.target.checked
    })
    this.setState({modules: modules})
  }

  customInputSwitched(buttonName, e) {
    let newStr = `we received ${e.target.checked} for ${buttonName}...`;
    console.log(newStr);
    let newLog = [...this.state.log, newStr];
    this.setState({ log: newLog });
  }

  render() {
    return (
      <div>
        <p>Start editing to see some magic happen :)</p>
        <Form>
          <FormGroup>
            <Label for="exampleCheckbox">Switches</Label>
            {this.state.modules.map((module, index) => {
              //console.log(disease, index);
              let idName = "exampleCustomSwitch"+index;

              return (
              <div key={index}>
                <CustomInput
                type="switch"
                id={idName}
                name="customSwitch"
                label={module.label}
                onChange={this.customInputSwitched.bind(this, button.value)}
              />
              </div>
              )
            }

            )}
          </FormGroup>
        </Form>
        {this.state.log}
      </div>
    );
  }
}

export default ModuleSettings
