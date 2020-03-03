import React, { Component } from 'react'
import LineItem from './LineItem'
import { Button, FormGroup, Label, Input } from 'reactstrap'
import axios from 'axios'

class LineItemEditor extends Component {
    constructor (props) {
        super(props)
        this.state = {
            rowData: [],
            products: [],
            taxRates: [],
            expenses: [],
            line_type: 1,
            total: this.props.total
        }

        this.handleRowChange = this.handleRowChange.bind(this)
        this.handleRowDelete = this.handleRowDelete.bind(this)
        this.handleRowAdd = this.handleRowAdd.bind(this)
        this.loadProducts = this.loadProducts.bind(this)
        this.loadTaxRates = this.loadTaxRates.bind(this)
        this.loadExpenses = this.loadExpenses.bind(this)
        this.handleLineTypeChange = this.handleLineTypeChange.bind(this)
    }

    componentDidMount () {
        this.loadProducts()
        this.loadTaxRates()
        this.loadExpenses()
    }

    loadProducts () {
        axios.get('/api/products').then(data => {
            this.setState({ products: data.data })
        })
    }

    loadTaxRates () {
        axios.get('/api/taxRates').then(data => {
            this.setState({ taxRates: data.data })
        })
    }

    loadExpenses () {
        axios.get('/api/expenses').then(data => {
            this.setState({ expenses: data.data })
        })
    }

    handleLineTypeChange (e) {
        this.setState({ line_type: e.target.value })
    }

    handleRowChange (e) {
        const rows = [...this.props.rows]

        if (e.target.name.includes('task_id')) {
            const test = e.target.name.split('|')
            const row = test[0]

            rows[row].task_id = e.target.value
            rows[row].quantity = 1
            this.props.update(rows, row)

            return
        }

        const row = e.target.dataset.line

        if (e.target.name === 'unit_tax') {
            const index = this.state.taxRates.findIndex(taxRate => taxRate.id === parseInt(e.target.value))
            const taxRate = this.state.taxRates[index]
            rows[row].tax_rate_id = taxRate.id
            rows[row].tax_rate_name = taxRate.name
            rows[row].unit_tax = taxRate.rate
            this.props.update(rows, row)

            return
        }

        if (e.target.name === 'product_id') {
            const index = this.state.products.findIndex(product => product.id === parseInt(e.target.value))
            const product = this.state.products[index]

            rows[row].unit_price = product.price
            rows[row].product_id = e.target.value
            this.props.update(rows, row)

            return
        }

        if (e.target.name === 'expense_id') {
            const index = this.state.expenses.findIndex(expense => expense.id === parseInt(e.target.value))
            const expense = this.state.expenses[index]

            rows[row].expense_id = e.target.value
            rows[row].unit_price = expense.amount
            rows[row].quantity = 1
            this.props.update(rows, row)

            return
        }

        rows[row][e.target.name] = e.target.value
        this.props.update(rows, row)
    }

    handleRowDelete (index) {
        this.props.delete(index)
    }

    handleRowAdd () {
        this.props.onAddFiled()
    }

    render () {
        const lineItemRows = this.state.products.length && this.state.expenses.length && this.state.taxRates.length
            ? <LineItem
                line_type={parseInt(this.state.line_type)}
                rows={this.props.rows}
                tax_rates={this.state.taxRates}
                expenses={this.state.expenses}
                products={this.state.products}
                new={true}
                onChange={this.handleRowChange}
                handleTaskChange={this.updateTasks}
                onDelete={this.handleRowDelete}
            />
            : null

        let total = this.props.sub_total - this.props.discount_total
        total = total += this.props.tax_total

        return (
            <React.Fragment>

                <FormGroup>
                    <Label>Tax</Label>
                    <Input name="line_type" type='select' value={this.state.line_type}
                        onChange={this.handleLineTypeChange} className='pa2 mr2 f6 form-control'>
                        <option value="1">Product</option>
                        <option value="2">Task</option>
                        <option value="3">Expense</option>
                    </Input>
                </FormGroup>
                {lineItemRows}

                <table id='lines-table'>
                    <tfoot>
                        <tr>
                            <th/>
                            <th>Tax total:</th>
                            <th>{this.props.tax_total}</th>
                            <th/>
                        </tr>

                        <tr>
                            <th/>
                            <th>Discount total:</th>
                            <th>{this.props.discount_total}</th>
                            <th/>
                        </tr>

                        <tr>
                            <th/>
                            <th>Sub total:</th>
                            <th>{this.props.sub_total}</th>
                            <th/>
                        </tr>

                        <tr>
                            <th/>
                            <th>Grand total:</th>
                            <th>{total}</th>
                            <th/>
                        </tr>
                    </tfoot>
                </table>

                <Button color="success" onClick={this.handleRowAdd}
                    className='f6 link dim ph3 pv1 mb2 dib white bg-dark-green bn'>Add
                </Button>
            </React.Fragment>
        )
    }
}

export default LineItemEditor
