import React, { Component } from 'react'
import axios from 'axios'
import { Input, FormGroup } from 'reactstrap'

export default class QuoteDropdown extends Component {
    constructor (props) {
        super(props)
        this.state = {
            quotes: []
        }

        this.getQuotes = this.getQuotes.bind(this)
    }

    componentDidMount () {
        if (!this.props.quotes || !this.props.quotes.length) {
            this.getQuotes()
        } else {
            this.state.quotes = this.props.quotes
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

    getQuotes () {
        const url = 'api/quote/'

        axios.get(url)
            .then((r) => {
                this.setState({
                    quotes: r.data
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    render () {
        let quoteList = null

        const quotes = this.props.quotes ? this.props.quotes : this.state.quotes

        if (!quotes) {
            quoteList = <option value="">Loading...</option>
        } else {
            quoteList = quotes.map((quote, index) => (
                <option key={index} value={quote.id}>{quote.id}</option>
            ))
        }

        const name = this.props.name && this.props.name ? this.props.name : 'quotes_id'

        const selectList = this.props.multiple && this.props.multiple === true ? (
            <Input onChange={this.props.handleInputChanges} multiple type="select"
                name={name} id={name}>
                {quoteList}
            </Input>
        ) : <Input value={this.props.quote_id} onChange={this.props.handleInputChanges} type="select"
            name={name} id={name}>
            <option value="">Select Quote</option>
            {quoteList}
        </Input>

        return (
            <FormGroup>
                {selectList}
                {this.renderErrorFor(name)}
            </FormGroup>
        )
    }
}
