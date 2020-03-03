import React, { Component } from 'react'
import InvoiceLineInputs from './InvoiceLineInputs'

export default class InvoiceLine extends Component {
    constructor (props) {
        super(props)
        this.state = {
            lines: this.props.lines && this.props.lines.length ? this.props.lines : [{ invoice_id: null, amount: 0 }],
            amount: 0,
            customer_id: null
        }

        this.handleChange = this.handleChange.bind(this)
        this.addLine = this.addLine.bind(this)
        this.removeLine = this.removeLine.bind(this)
    }

    handleChange (e) {
        const name = e.target.name
        const idx = e.target.dataset.id

        const lines = [...this.state.lines]
        let amount = 0

        if (name === 'invoice_id') {
            const invoice = this.props.invoices.filter(function (invoice) {
                return invoice.id === parseInt(e.target.value)
            })

            if (!invoice.length) {
                return
            }

            this.props.customerChange(invoice[0].customer_id)
            lines[idx].amount = parseFloat(invoice[0].total)
            amount = this.state.amount += parseFloat(invoice[0].total)
        }

        lines[e.target.dataset.id][e.target.name] = e.target.value
        this.setState({ lines }, () => {
            this.props.onChange(this.state.lines)

            if (amount > 0) {
                this.props.handleAmountChange(amount)
            }

            const nextIndex = parseInt(idx) === 0 ? 2 : parseInt(idx) + 2

            if (this.state.lines.length < nextIndex) {
                this.addLine(e)
            }
        }
        )
    }

    addLine (e) {
        const allowedInvoices = this.props.status ? this.props.invoices.filter(invoice => invoice.status_id === this.props.status) : this.props.invoices

        if (this.state.lines.length >= allowedInvoices.length) {
            return
        }

        this.setState((prevState) => ({
            lines: [...prevState.lines, { invoice_id: null, amount: 0 }]
        }), () => this.props.onChange(this.state.lines))
    }

    removeLine (idx) {
        if (this.state.lines.length === 1) {
            return
        }

        this.setState({
            lines: this.state.lines.filter(function (line, sidx) {
                return sidx !== idx
            })
        }, () => this.props.onChange(this.state.lines))
    }

    render () {
        const { lines } = this.state
        const status = this.props.status ? this.props.status : null
        const invoices = this.props.allInvoices ? this.props.allInvoices : []
        return (
            <form>
                <InvoiceLineInputs invoices={invoices} status={status} errors={this.props.errors}
                    onChange={this.handleChange} lines={lines}
                    removeLine={this.removeLine}/>
                {/* <Button color="primary" onClick={this.addLine}>Add</Button> */}
            </form>
        )
    }
}
