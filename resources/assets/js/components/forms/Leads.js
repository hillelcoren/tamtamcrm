import React, { Component } from 'react'
import axios from 'axios'
import EditLead from './EditLeadForm'
import AddLead from './AddLeadForm'
import DataTable from '../common/DataTable'
import RestoreModal from '../common/RestoreModal'
import DeleteModal from '../common/DeleteModal'
import {
    FormGroup, Input, Card, CardBody, Row, Col
} from 'reactstrap'
import DisplayColumns from '../common/DisplayColumns'
import CustomerDropdown from '../common/CustomerDropdown'
import ActionsMenu from '../common/ActionsMenu'
import TableSearch from '../common/TableSearch'
import FilterTile from '../common/FilterTile'
import ViewEntity from '../common/ViewEntity'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import BulkActionDropdown from '../common/BulkActionDropdown'

export default class Leads extends Component {
    constructor (props) {
        super(props)

        this.state = {
            leads: [],
            cachedData: [],
            errors: [],
            bulk: [],
            dropdownButtonActions: ['download'],
            error: '',
            view: {
                viewMode: false,
                viewedId: null,
                title: null
            },
            filters: {
                status_id: 'active',
                customer_id: '',
                searchText: '',
                start_date: '',
                end_date: ''
            },
            custom_fields: [],
            ignoredColumns: [
                'created_at',
                'deleted_at',
                'updated_at',
                'address_1',
                'address_2',
                'is_deleted',
                'archived_at',
                'account_id',
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4',
                'city',
                'zip',
                'source_type',
                'valued_at',
                'company_name',
                'job_title',
                'website',
                'private_notes',
                'public_notes',
                'user_id',
                'assigned_user_id',
                'task_status',
                'id'
            ],
            showRestoreButton: false
        }

        this.addUserToState = this.addUserToState.bind(this)
        this.userList = this.userList.bind(this)
        this.deleteLead = this.deleteLead.bind(this)
        this.filterLeads = this.filterLeads.bind(this)
        this.updateIgnoredColumns = this.updateIgnoredColumns.bind(this)
        this.getFilters = this.getFilters.bind(this)
        this.toggleViewedEntity = this.toggleViewedEntity.bind(this)
        this.onChangeBulk = this.onChangeBulk.bind(this)
        this.saveBulk = this.saveBulk.bind(this)
    }

    componentDidMount () {
        this.getUsers()
        this.getCustomFields()
    }

    updateIgnoredColumns (columns) {
        this.setState({ ignoredColumns: columns.concat('settings') }, function () {
            console.log('ignored columns', this.state.ignoredColumns)
        })
    }

    addUserToState (leads) {
        const cachedData = !this.state.cachedData.length ? leads : this.state.cachedData
        this.setState({
            leads: leads,
            cachedData: cachedData
        })
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
        axios.post('/api/lead/bulk', { bulk: this.state.bulk, action: action }).then(function (response) {
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

    filterLeads (event) {
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

    getFilters () {
        const { status_id, searchText, start_date, end_date } = this.state.filters
        const columnFilter = this.state.leads.length
            ? <DisplayColumns onChange={this.updateIgnoredColumns} columns={Object.keys(this.state.leads[0])}
                ignored_columns={this.state.ignoredColumns}/> : null
        return (
            <Row form>
                <Col md={3}>
                    <TableSearch onChange={this.filterLeads}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <Input type='select'
                            onChange={this.filterLeads}
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
                    <BulkActionDropdown
                        dropdownButtonActions={this.state.dropdownButtonActions}
                        saveBulk={this.saveBulk}/>
                </Col>

                <Col>
                    <CsvImporter filename="leads.csv"
                        url={`/api/leads?search_term=${searchText}&status=${status_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <DateFilter onChange={this.filterLeads} update={this.addUserToState}
                            data={this.state.cachedData}/>
                    </FormGroup>
                </Col>

                <Col md={8}>
                    {columnFilter}
                </Col>
            </Row>
        )
    }

    userList () {
        const { leads, custom_fields, users, ignoredColumns } = this.state
        if (leads && leads.length) {
            return leads.map(lead => {
                const restoreButton = lead.deleted_at
                    ? <RestoreModal id={lead.id} entities={leads} updateState={this.addUserToState}
                        url={`/api/leads/restore/${lead.id}`}/> : null
                const archiveButton = !lead.deleted_at
                    ? <DeleteModal archive={true} deleteFunction={this.deleteLead} id={lead.id}/> : null
                const deleteButton = !lead.deleted_at
                    ? <DeleteModal archive={false} deleteFunction={this.deleteLead} id={lead.id}/> : null
                const editButton = !lead.deleted_at ? <EditLead
                    listView={true}
                    custom_fields={custom_fields}
                    users={users}
                    lead={lead}
                    leads={leads}
                    action={this.addUserToState}
                /> : null

                const columnList = Object.keys(lead).filter(key => {
                    return ignoredColumns && !ignoredColumns.includes(key)
                }).map(key => {
                    return <td onClick={() => this.toggleViewedEntity(lead, lead.title)} data-label={key}
                        key={key}>{lead[key]}</td>
                })
                return <tr key={lead.id}>
                    <td>
                        <Input value={lead.id} type="checkbox" onChange={this.onChangeBulk} />
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

    deleteLead (id, archive = true) {
        const self = this
        const url = archive === true ? `/api/leads/archive/${id}` : `/api/leads/${id}`

        axios.delete(url)
            .then(function (response) {
                const arrLeads = [...self.state.leads]
                const index = arrLeads.findIndex(lead => lead.id === id)
                arrLeads.splice(index, 1)
                self.addUserToState(arrLeads)
            })
            .catch(function (error) {
                console.log(error)
                self.setState(
                    {
                        error: error.response.data
                    }
                )
            })
    }

    getCustomFields () {
        axios.get('api/accounts/fields/Lead')
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

    getUsers () {
        axios.get('api/users')
            .then((r) => {
                this.setState({
                    users: r.data
                })
            })
            .catch((e) => {
                this.setState({
                    loading: false,
                    err: e
                })
            })
    }

    render () {
        const { leads, users, custom_fields, ignoredColumns, view } = this.state
        const { status_id, searchText, start_date, end_date } = this.state.filters
        const fetchUrl = `/api/leads?search_term=${searchText}&status=${status_id}&start_date=${start_date}&end_date=${end_date}`
        const { error } = this.state
        const filters = this.getFilters()

        return (
            <div className="data-table">

                {error && <div className="alert alert-danger" role="alert">
                    {error}
                </div>}

                <Card>
                    <CardBody>
                        <FilterTile filters={filters}/>
                        <AddLead users={users} leads={leads} action={this.addUserToState}
                            custom_fields={custom_fields}/>

                        <DataTable
                            disableSorting={['id']}
                            defaultColumn='title'
                            ignore={ignoredColumns}
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
