import React, { Component } from 'react'
import axios from 'axios'
import AddCustomer from './AddCustomer'
import EditCustomer from './EditCustomer'
import {
    FormGroup, Input, Card, CardBody, Row, Col, ButtonDropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem
} from 'reactstrap'
import DataTable from '../common/DataTable'
import CustomerTypeDropdown from '../common/CustomerTypeDropdown'
import CompanyDropdown from '../common/CompanyDropdown'
import DeleteModal from '../common/DeleteModal'
import RestoreModal from '../common/RestoreModal'
import DisplayColumns from '../common/DisplayColumns'
import CustomerGroupDropdown from '../common/CustomerGroupDropdown'
import ActionsMenu from '../common/ActionsMenu'
import TableSearch from '../common/TableSearch'
import FilterTile from '../common/FilterTile'
import ViewEntity from '../common/ViewEntity'
import CustomerPresenter from '../presenters/CustomerPresenter'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import BulkActionDropdown from '../common/BulkActionDropdown'

export default class Customers extends Component {
    constructor (props) {
        super(props)
        this.state = {
            per_page: 5,
            view: {
                viewMode: false,
                viewedId: null,
                title: null
            },
            customers: [],
            cachedData: [],
            companies: [],
            bulk: [],
            dropdownButtonActions: ['download'],
            filters: {
                status: 'active',
                company_id: '',
                customer_type: '',
                group_settings_id: '',
                searchText: '',
                start_date: '',
                end_date: ''
            },
            ignoredColumns: [
                'public_notes',
                'private_notes',
                'industry_id',
                'size_id',
                'created_at',
                'contacts',
                'deleted_at',
                'credit_balance',
                'settings',
                'assigned_user',
                'first_name',
                'last_name',
                'company',
                'customer_type',
                'company_id',
                'currency_id',
                'customer_type',
                'customerType',
                'credit',
                'job_title',
                'default_payment_method',
                'billing',
                'shipping',
                'currency',
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4',
                'group_settings_id',
                'paid_to_date'
                // 'phone'
            ],
            custom_fields: [],
            error: '',
            showRestoreButton: false
        }

        this.updateCustomers = this.updateCustomers.bind(this)
        this.customerList = this.customerList.bind(this)
        this.getCompanies = this.getCompanies.bind(this)
        this.filterCustomers = this.filterCustomers.bind(this)
        this.deleteCustomer = this.deleteCustomer.bind(this)
        this.updateIgnoredColumns = this.updateIgnoredColumns.bind(this)
        this.getFilters = this.getFilters.bind(this)
        this.toggleViewedEntity = this.toggleViewedEntity.bind(this)
        this.onChangeBulk = this.onChangeBulk.bind(this)
        this.saveBulk = this.saveBulk.bind(this)
    }

    componentDidMount () {
        this.getCompanies()
        this.getCustomFields()
    }

    updateCustomers (customers) {
        const cachedData = !this.state.cachedData.length ? customers : this.state.cachedData
        this.setState({
            customers: customers,
            cachedData: cachedData
        })
    }

    updateIgnoredColumns (columns) {
        this.setState({ ignoredColumns: columns.concat('customer', 'company', 'billing', 'shipping') }, function () {
            console.log('ignored columns', this.state.ignoredColumns)
        })
    }

    getCompanies () {
        axios.get('/api/companies')
            .then((r) => {
                this.setState({
                    companies: r.data
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    onChangeBulk (e) {
        // current array of options
        const options = this.state.bulk
        let index

        // check if the check box is checked or unchecked
        if (e.target.checked) {
            // add the numerical value of the checkbox to options array
            options.push(e.target.value)
        } else {
            // or remove the value from the unchecked checkbox from the array
            index = options.indexOf(+e.target.value)
            options.splice(index, 1)
        }

        // update the state with the new array of options
        this.setState({ bulk: options })
    }

    saveBulk () {
        const action = e.target.id
        const self = this
        axios.post('/api/customer/bulk', { bulk: this.state.bulk }).then(function (response) {
            // const arrQuotes = [...self.state.invoices]
            // const index = arrQuotes.findIndex(payment => payment.id === id)
            // arrQuotes.splice(index, 1)
            // self.updateInvoice(arrQuotes)
        })
            .catch(function (error) {
                self.setState(
                    {
                        error: error.response.data
                    }
                )
            })
    }

    getCustomFields () {
        axios.get('api/accounts/fields/Customer')
            .then((r) => {
                this.setState({
                    custom_fields: r.data.fields
                })
            })
            .catch((e) => {
                this.setState({
                    loading: false,
                    err: e
                })
            })
    }

    renderErrorFor () {

    }

    filterCustomers (event) {
        if ('start_date' in event) {
            this.setState(prevState => ({
                filters: {
                    ...prevState.filters,
                    start_date: event.start_date,
                    end_date: event.end_date
                }
            }))
            return
        }

        const column = event.target.id
        const value = event.target.value

        if (value === 'all') {
            const updatedRowState = this.state.filters.filter(filter => filter.column !== column)
            this.setState({ filters: updatedRowState })
            return true
        }

        const showRestoreButton = column === 'status' && value === 'archived'

        this.setState(prevState => ({
            filters: {
                ...prevState.filters,
                [column]: value
            },
            showRestoreButton: showRestoreButton
        }))

        return true
    }

    getFilters () {
        const { searchText, status, company_id, customer_type, group_settings_id, start_date, end_date } = this.state.filters
        const columnFilter = this.state.customers.length
            ? <DisplayColumns onChange2={this.updateIgnoredColumns} columns={Object.keys(this.state.customers[0])}
                ignored_columns={this.state.ignoredColumns}/> : null
        return (
            <Row form>
                <Col md={2}>
                    <TableSearch onChange={this.filterCustomers}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <Input type='select'
                            onChange={this.filterCustomers}
                            name="status"
                            id="status"
                        >
                            <option value="">Select Status</option>
                            <option value='active'>Active</option>
                            <option value='archived'>Archived</option>
                            <option value='deleted'>Deleted</option>
                        </Input>
                    </FormGroup>
                </Col>

                <Col md={3}>
                    <CompanyDropdown
                        company_id={this.state.filters.company_id}
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterCustomers}
                    />
                </Col>

                <Col md={3}>
                    <CustomerGroupDropdown
                        customer_group={this.state.filters.group_settings_id}
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterCustomers}
                    />
                </Col>

                <Col md={2}>
                    <CustomerTypeDropdown
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterCustomers}
                    />
                </Col>

                <Col md={1}>
                    <CsvImporter filename="customers.csv"
                        url={`/api/customers?search_term=${searchText}&status=${status}&company_id=${company_id}&customer_type=${customer_type}&group_settings_id=${group_settings_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col md={2}>
                    <BulkActionDropdown
                        dropdownButtonActions={this.state.dropdownButtonActions}
                        saveBulk={this.saveBulk}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <DateFilter onChange={this.filterCustomers} update={this.updateCustomers}
                            data={this.state.cachedData}/>
                    </FormGroup>
                </Col>

                <Col md={6}>
                    <FormGroup>
                        {columnFilter}
                    </FormGroup>
                </Col>
            </Row>
        )
    }

    toggleViewedEntity (id, title = null) {
        this.setState({
            view: {
                ...this.state.view,
                viewMode: !this.state.view.viewMode,
                viewedId: id,
                title: title
            }
        }, () => console.log('view', this.state.view))
    }

    customerList () {
        const { customers, custom_fields, ignoredColumns } = this.state
        if (customers && customers.length) {
            return customers.map(customer => {
                const restoreButton = customer.deleted_at
                    ? <RestoreModal id={customer.id} entities={customers} updateState={this.updateCustomers}
                        url={`/api/customers/restore/${customer.id}`}/> : null
                const archiveButton = !customer.deleted_at
                    ? <DeleteModal archive={true} deleteFunction={this.deleteCustomer} id={customer.id}/> : null
                const deleteButton = !customer.deleted_at
                    ? <DeleteModal archive={false} deleteFunction={this.deleteCustomer} id={customer.id}/> : null
                const editButton = !customer.deleted_at && this.state.customers.length ? <EditCustomer
                    custom_fields={custom_fields}
                    customer={customer}
                    action={this.updateCustomers}
                    customers={customers}
                    modal={true}
                /> : null

                const columnList = Object.keys(customer).filter(key => {
                    return ignoredColumns && !ignoredColumns.includes(key)
                }).map(key => {
                    return <CustomerPresenter toggleViewedEntity={this.toggleViewedEntity}
                        field={key} entity={customer}/>
                })

                return (
                    <tr key={customer.id}>
                        <td>
                            <Input value={customer.id} type="checkbox" onChange={this.onChangeBulk}/>
                            <ActionsMenu edit={editButton} delete={deleteButton} archive={archiveButton}
                                restore={restoreButton}/>
                        </td>
                        {columnList}
                    </tr>
                )
            })
        } else {
            return <tr>
                <td className="text-center">No Records Found.</td>
            </tr>
        }
    }

    deleteCustomer (id, archive = true) {
        const url = archive === true ? `/api/customers/archive/${id}` : `/api/customers/${id}`
        axios.delete(url).then(data => {
            const arrCustomers = [...this.state.customers]
            const index = arrCustomers.findIndex(customer => customer.id === id)
            arrCustomers.splice(index, 1)
            this.updateCustomers(arrCustomers)
        })
    }

    render () {
        const { searchText, status, company_id, customer_type, group_settings_id, start_date, end_date } = this.state.filters
        const { custom_fields, customers, companies, error, view } = this.state
        const fetchUrl = `/api/customers?search_term=${searchText}&status=${status}&company_id=${company_id}&customer_type=${customer_type}&group_settings_id=${group_settings_id}&start_date=${start_date}&end_date=${end_date}`
        const filters = this.getFilters()
        const addButton = companies.length ? <AddCustomer
            custom_fields={custom_fields}
            customer_type={this.props.customer_type}
            action={this.updateCustomers}
            customers={customers}
            companies={companies}
        /> : null

        return (
            <div className="data-table">

                {error && <div className="alert alert-danger" role="alert">
                    {error}
                </div>}

                <Card>
                    <CardBody>
                        <FilterTile filters={filters}/>
                        {addButton}

                        <DataTable
                            disableSorting={['id']}
                            defaultColumn='name'
                            userList={this.customerList}
                            ignore={this.state.ignoredColumns}
                            fetchUrl={fetchUrl}
                            updateState={this.updateCustomers}
                        />
                    </CardBody>
                </Card>

                <ViewEntity ignore={[]} toggle={this.toggleViewedEntity} title={view.title}
                    viewed={view.viewMode}
                    entity={view.viewedId}/>
            </div>
        )
    }
}
