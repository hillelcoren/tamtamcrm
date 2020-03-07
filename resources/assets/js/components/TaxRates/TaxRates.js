import React, { Component } from 'react'
import axios from 'axios'
import EditTaxRate from './EditTaxRate'
import AddTaxRate from './AddTaxRate'
import DataTable from '../common/DataTable'
import DeleteModal from '../common/DeleteModal'
import RestoreModal from '../common/RestoreModal'
import {
    Card, CardBody, FormGroup, Input, Row, Col
} from 'reactstrap'
import DisplayColumns from '../common/DisplayColumns'
import ActionsMenu from '../common/ActionsMenu'
import TableSearch from '../common/TableSearch'
import FilterTile from '../common/FilterTile'
import ViewEntity from '../common/ViewEntity'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import BulkActionDropdown from '../common/BulkActionDropdown'

export default class TaxRates extends Component {
    constructor (props) {
        super(props)

        this.state = {
            taxRates: [],
            cachedData: [],
            dropdownButtonActions: ['download'],
            filters: {
                status_id: 'active',
                searchText: '',
                start_date: '',
                end_date: ''
            },
            view: {
                viewMode: false,
                viewedId: null,
                title: null
            },
            ignoredColumns: [
                'created_at',
                'deleted_at',
                'updated_at'
            ],
            errors: [],
            error: '',
            showRestoreButton: false
        }

        this.addUserToState = this.addUserToState.bind(this)
        this.userList = this.userList.bind(this)
        this.deleteTaxRate = this.deleteTaxRate.bind(this)
        this.filterTaxRates = this.filterTaxRates.bind(this)
        this.updateIgnoredColumns = this.updateIgnoredColumns.bind(this)
        this.getFilters = this.getFilters.bind(this)
        this.toggleViewedEntity = this.toggleViewedEntity.bind(this)
    }

    addUserToState (taxRates) {
        const cachedData = !this.state.cachedData.length ? taxRates : this.state.cachedData
        this.setState({
            taxRates: taxRates,
            cachedData: cachedData
        })
    }

    updateIgnoredColumns (columns) {
        this.setState({ ignoredColumns: columns.concat('customer') }, function () {
            console.log('ignored columns', this.state.ignoredColumns)
        })
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

    onChangeBulk (e) {
        // current array of options
        const options = this.state.bulk
        let index

        // check if the check box is checked or unchecked
        if (e.target.checked) {
            // add the numerical value of the checkbox to options array
            options.push(+e.target.value)
        } else {
            // or remove the value from the unchecked checkbox from the array
            index = options.indexOf(e.target.value)
            options.splice(index, 1)
        }

        // update the state with the new array of options
        this.setState({ bulk: options })
    }

    saveBulk (e) {
        const action = e.target.id
        const self = this
        axios.post('/api/user/bulk', { ids: this.state.bulk, action: action }).then(function (response) {
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

    filterTaxRates (event) {
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

        const showRestoreButton = column === 'status_id' && value === 'archived'

        this.setState(prevState => ({
            filters: {
                ...prevState.filters,
                [column]: value
            },
            showRestoreButton: showRestoreButton
        }))

        return true
    }

    resetFilters () {
        this.props.reset()
    }

    getFilters () {
        const { searchText, status_id, start_date, end_date } = this.state.filters
        const columnFilter = this.state.taxRates.length
            ? <DisplayColumns onChange2={this.updateIgnoredColumns} columns={Object.keys(this.state.taxRates[0])}
                ignored_columns={this.state.ignoredColumns}/> : null
        return (
            <Row form>
                <Col md={3}>
                    <TableSearch onChange={this.filterTaxRates}/>
                </Col>

                <Col md={2}>
                    <FormGroup className="mb-2 mr-sm-2 mb-sm-0">
                        <Input type='select'
                            onChange={this.filterTaxRates}
                            id="status_id"
                            name="status_id"
                        >
                            <option value="">Select Status</option>
                            <option value='active'>Active</option>
                            <option value='archived'>Archived</option>
                            <option value='deleted'>Deleted</option>
                        </Input>
                    </FormGroup>
                </Col>

                <Col>
                    <CsvImporter filename="taxRates.csv"
                        url={`/api/taxRates?search_term=${searchText}&status=${status_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col>
                    <BulkActionDropdown
                        dropdownButtonActions={this.state.dropdownButtonActions}
                        saveBulk={this.saveBulk}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <DateFilter onChange={this.filterTaxRates} update={this.addUserToState}
                            data={this.state.cachedData}/>
                    </FormGroup>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        {columnFilter}
                    </FormGroup>
                </Col>
            </Row>
        )
    }

    userList () {
        const { taxRates, ignoredColumns } = this.state
        if (taxRates && taxRates.length) {
            return taxRates.map(taxRate => {
                const restoreButton = taxRate.deleted_at
                    ? <RestoreModal id={taxRate.id} entities={taxRates} updateState={this.addUserToState}
                        url={`/api/taxRate/restore/${taxRate.id}`}/> : null

                const deleteButton = !taxRate.deleted_at
                    ? <DeleteModal archive={false} deleteFunction={this.deleteTaxRate} id={taxRate.id}/> : null

                const archiveButton = !taxRate.deleted_at
                    ? <DeleteModal archive={true} deleteFunction={this.deleteTaxRate} id={taxRate.id}/> : null

                const editButton = !taxRate.deleted_at
                    ? <EditTaxRate taxRate={taxRate} taxRates={taxRates} action={this.addUserToState}/>
                    : null

                const columnList = Object.keys(taxRate).filter(key => {
                    return ignoredColumns && !ignoredColumns.includes(key)
                }).map(key => {
                    return <td onClick={() => this.toggleViewedEntity(taxRate, taxRate.name)} data-label={key}
                        key={key}>{taxRate[key]}</td>
                })

                return <tr key={taxRate.id}>
                    <td>
                        <ActionsMenu edit={editButton} delete={deleteButton} archive={archiveButton}
                            restore={restoreButton}/>
                    </td>
                    {columnList}
                </tr>
            })
        } else {
            return <tr>
                <td className="text-center">No Records Found.</td>
            </tr>
        }
    }

    deleteTaxRate (id, archive = true) {
        const url = archive === true ? `/api/taxRates/archive/${id}` : `/api/taxRates/${id}`
        const self = this
        axios.delete(url)
            .then(function (response) {
                const arrTaxRates = [...self.state.taxRates]
                const index = arrTaxRates.findIndex(taxRate => taxRate.id === id)
                arrTaxRates.splice(index, 1)
                self.addUserToState(arrTaxRates)
            })
            .catch(function (error) {
                self.setState(
                    {
                        error: error.response.data
                    }
                )
            })
    }

    render () {
        const { taxRates, error, view } = this.state
        const { searchText, status_id, start_date, end_date } = this.state.filters
        const fetchUrl = `/api/taxRates?search_term=${searchText}&status=${status_id}&start_date=${start_date}&end_date=${end_date}`
        const filters = this.getFilters()
        const addButton = <AddTaxRate taxRates={taxRates} action={this.addUserToState}/>

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
                            ignore={this.state.ignoredColumns}
                            disableSorting={['id']}
                            defaultColumn='name'
                            userList={this.userList}
                            fetchUrl={fetchUrl}
                            updateState={this.addUserToState}
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
