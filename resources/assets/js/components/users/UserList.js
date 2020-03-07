import React, { Component } from 'react'
import axios from 'axios'
import EditUser from './EditUser'
import AddUser from './AddUser'
import {
    FormGroup, Input, Card, CardBody, Row, Col
} from 'reactstrap'
import DataTable from '../common/DataTable'
import DepartmentDropdown from '../common/DepartmentDropdown'
import RoleDropdown from '../common/RoleDropdown'
import RestoreModal from '../common/RestoreModal'
import DeleteModal from '../common/DeleteModal'
import DisplayColumns from '../common/DisplayColumns'
import ActionsMenu from '../common/ActionsMenu'
import TableSearch from '../common/TableSearch'
import FilterTile from '../common/FilterTile'
import ViewEntity from '../common/ViewEntity'
import UserPresenter from '../presenters/UserPresenter'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import BulkActionDropdown from '../common/BulkActionDropdown'

export default class UserList extends Component {
    constructor (props) {
        super(props)
        this.state = {
            users: [],
            cachedData: [],
            departments: [],
            accounts: [],
            custom_fields: [],
            bulk: [],
            dropdownButtonActions: ['download'],
            error: '',
            view: {
                viewMode: false,
                viewedId: null,
                title: null
            },
            filters: {
                start_date: '',
                end_date: '',
                status: 'active',
                role_id: '',
                department_id: '',
                searchText: ''
            },
            ignoredColumns: [
                'account_users',
                'department',
                'phone_number',
                'job_description',
                'gender',
                'dob',
                'username',
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4',
                'deleted_at',
                'created_at'
            ],
            showRestoreButton: false
        }

        this.cachedResults = []
        this.addUserToState = this.addUserToState.bind(this)
        this.userList = this.userList.bind(this)
        this.filterUsers = this.filterUsers.bind(this)
        this.deleteUser = this.deleteUser.bind(this)
        this.updateIgnoredColumns = this.updateIgnoredColumns.bind(this)
        this.getFilters = this.getFilters.bind(this)
        this.toggleViewedEntity = this.toggleViewedEntity.bind(this)
        this.onChangeBulk = this.onChangeBulk.bind(this)
        this.saveBulk = this.saveBulk.bind(this)
        this.getAccounts = this.getAccounts.bind(this)
        this.getDepartments = this.getDepartments.bind(this)
    }

    componentDidMount () {
        this.getDepartments()
        this.getAccounts()
        this.getCustomFields()
    }

    updateIgnoredColumns (columns) {
        this.setState({ ignoredColumns: columns.concat('customer', 'account_users') }, function () {
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
        axios.post(`/api/user/bulk/${action}`, { ids: this.state.bulk }).then(function (response) {
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

    filterUsers (event) {
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

    renderErrorFor () {

    }

    getFilters () {
        const { departments } = this.state
        const { status, role_id, department_id, searchText, start_date, end_date } = this.state.filters
        const columnFilter = this.state.users.length
            ? <DisplayColumns onChange2={this.updateIgnoredColumns} columns={Object.keys(this.state.users[0])}
                ignored_columns={this.state.ignoredColumns}/> : null
        return (
            <Row form>
                <Col md={3}>
                    <TableSearch onChange={this.filterUsers}/>
                </Col>

                <Col md={3}>
                    <DepartmentDropdown
                        name="department_id"
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterUsers}
                        departments={departments}
                    />
                </Col>

                <Col md={2}>
                    <RoleDropdown
                        name="role_id"
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterUsers}
                    />
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <Input type='select'
                            onChange={this.filterUsers}
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

                <Col>
                    <CsvImporter filename="users.csv"
                        url={`/api/users?search_term=${searchText}&status=${status}&role_id=${role_id}&department_id=${department_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col>
                    <BulkActionDropdown
                        dropdownButtonActions={this.state.dropdownButtonActions}
                        saveBulk={this.saveBulk}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <DateFilter onChange={this.filterUsers} update={this.addUserToState}
                            data={this.state.cachedData}/>
                    </FormGroup>
                </Col>

                <Col md={8}>
                    <FormGroup>
                        {columnFilter}
                    </FormGroup>
                </Col>
            </Row>
        )
    }

    getCustomFields () {
        axios.get('api/accounts/fields/User')
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

    getAccounts () {
        axios.get('/api/accounts')
            .then((r) => {
                console.log('accounts', r.data)
                this.setState({
                    accounts: r.data
                })
            })
            .catch((e) => {
                alert(e)
                console.error(e)
            })
    }

    getDepartments () {
        axios.get('/api/departments')
            .then((r) => {
                this.setState({
                    departments: r.data
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    addUserToState (users) {
        const cachedData = !this.state.cachedData.length ? users : this.state.cachedData
        this.setState({
            users: users,
            cachedData: cachedData
        })
    }

    userList () {
        const { users, departments, custom_fields, ignoredColumns } = this.state

        if (users && users.length) {
            return users.map(user => {
                const restoreButton = user.deleted_at
                    ? <RestoreModal id={user.id} entities={users} updateState={this.addUserToState}
                        url={`/api/users/restore/${user.id}`}/> : null
                const archiveButton = !user.deleted_at
                    ? <DeleteModal archive={true} deleteFunction={this.deleteUser} id={user.id}/> : null
                const deleteButton = !user.deleted_at
                    ? <DeleteModal archive={false} deleteFunction={this.deleteUser} id={user.id}/> : null
                const editButton = !user.deleted_at
                    ? <EditUser accounts={this.state.accounts} departments={departments} user_id={user.id}
                        custom_fields={custom_fields} users={users}
                        action={this.addUserToState}/> : null

                const columnList = Object.keys(user).filter(key => {
                    return ignoredColumns && !ignoredColumns.includes(key)
                }).map(key => {
                    return <UserPresenter key={key} toggleViewedEntity={this.toggleViewedEntity}
                        field={key} entity={user}/>
                })

                return <tr key={user.id}>
                    <td>
                        <Input value={user.id} type="checkbox" onChange={this.onChangeBulk}/>
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

    deleteUser (id, archive = true) {
        const url = archive === true ? `/api/users/archive/${id}` : `/api/users/${id}`
        const self = this
        axios.delete(url)
            .then(function (response) {
                const arrUsers = [...self.state.users]
                const index = arrUsers.findIndex(user => user.id === id)
                arrUsers.splice(index, 1)
                self.addUserToState(arrUsers)
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
        const { users, departments, custom_fields, error, view } = this.state
        const { status, role_id, department_id, searchText, start_date, end_date } = this.state.filters
        const fetchUrl = `/api/users?search_term=${searchText}&status=${status}&role_id=${role_id}&department_id=${department_id}&start_date=${start_date}&end_date=${end_date}`
        const filters = this.getFilters()
        const addButton = departments.length
            ? <AddUser accounts={this.state.accounts} custom_fields={custom_fields} departments={departments}
                users={users}
                action={this.addUserToState}/> : null

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
                            defaultColumn='last_name'
                            ignore={this.state.ignoredColumns}
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
