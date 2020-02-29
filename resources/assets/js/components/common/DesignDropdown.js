import React, { Component } from 'react'
import axios from 'axios'
import { FormGroup, Input } from 'reactstrap'

export default class DesignDropdown extends Component {
    constructor (props) {
        super(props)
        this.state = {
            designs: []
        }

        this.getDesigns = this.getDesigns.bind(this)
    }

    componentDidMount () {
        if (!this.props.designs || !this.props.designs.length) {
            this.getDesigns()
        } else {
            this.setState({ designs: this.props.designs })
        }
    }

    renderErrorFor (field) {
        if (this.hasErrorFor(field)) {
            return (
                <span className='invalid-feedback d-block'>
                    <strong>{this.props.errors[field][0]}</strong>
                </span>
            )
        }
    }

    hasErrorFor (field) {
        return this.props.errors && !!this.props.errors[field]
    }

    handleChange (value, name) {
        const e = {
            target: {
                id: name,
                name: name,
                value: value.id
            }
        }

        this.props.handleInputChanges(e)
    }

    getDesigns () {
        axios.get('/api/designs')
            .then((r) => {
                this.setState({
                    designs: r.data
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    render () {
        let designList = null
        if (!this.state.designs.length) {
            designList = <option value="">Loading...</option>
        } else {
            designList = this.state.designs.map((design, index) => (
                <option key={index} value={design.name}>{design.name}</option>
            ))
        }

        const name = this.props.name && this.props.name ? this.props.name : 'design'

        return (
            <FormGroup className="mr-2">
                <Input value={this.props.design} onChange={this.props.handleInputChanges} type="select"
                    name={name} id={name}>
                    <option value="">Choose Design</option>
                    {designList}
                </Input>
            </FormGroup>
        )
    }
}
