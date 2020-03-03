import React, { Component } from 'react'
import {
    Card, CardBody,
    CardHeader, Label, Input, Button,
    FormGroup, Modal, ModalHeader, ModalBody, ModalFooter, Nav,
    NavItem,
    NavLink,
    TabContent,
    TabPane,
    Dropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem
} from 'reactstrap'
import axios from 'axios'
import AddLead from './AddLead'
import 'react-dates/initialize' // necessary for latest version
import 'react-dates/lib/css/_datepicker.css'
import { DateRangePicker } from 'react-dates'
import FormBuilder from '../accounts/FormBuilder'
import moment from 'moment'
import EditTaskTimes from './EditTaskTimes'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'

class EditTask extends Component {
    constructor (props) {
        super(props)

        const start_date = this.props.task.start_date ? moment(this.props.task.start_date, 'YYYY-MM-DD') : moment()

        this.state = {
            modal: false,
            dropdownOpen: false,
            isOpen: false,
            changesMade: false,
            showSuccessMessage: false,
            showErrorMessage: false,
            title: this.props.task.title,
            description: this.props.task.content,
            due_date: moment(this.props.task.due_date),
            start_date: start_date,
            contributors: this.props.task.contributors,
            rating: this.props.task.rating,
            source_type: this.props.task.source_type,
            valued_at: this.props.task.valued_at,
            customer_id: this.props.task.customer_id,
            start_time: this.props.task.start_time ? this.props.task.start_time : null,
            duration: this.props.task.duration ? this.props.task.duration : null,
            err: '',
            action: null,
            users: [],
            custom_value1: this.props.task ? this.props.task.custom_value1 : '',
            custom_value2: this.props.task ? this.props.task.custom_value2 : '',
            custom_value3: this.props.task ? this.props.task.custom_value3 : '',
            custom_value4: this.props.task ? this.props.task.custom_value4 : '',
            selectedUsers: this.props.task.contributors ? this.props.task.contributors : [],
            activeTab: '1'
        }

        this.initialState = this.state
        this.handleSave = this.handleSave.bind(this)
        this.handleDelete = this.handleDelete.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleMultiSelect = this.handleMultiSelect.bind(this)
        this.timerAction = this.timerAction.bind(this)
        this.toggle = this.toggle.bind(this)
        this.toggleTab = this.toggleTab.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
    }

    toggle () {
        if (this.state.modal && this.state.changesMade) {
            if (window.confirm('Your changes have not been saved?')) {
                this.setState({ ...this.initialState })
            }

            return
        }

        this.setState({
            modal: !this.state.modal,
            errors: []
        })
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    toggleTab (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    timerAction (e) {
        axios.put(`/api/tasks/timer/${this.props.task.id}`, {
            action: e.target.id
        })
            .then((response) => {
                this.setState({ action: e.target.id })
            })
            .catch((error) => {
                this.setState({
                    err: error.response.data.errors
                })
            })
    }

    changeStatus (action) {
        if (!this.props.task.id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/task/${this.props.task.id}/${action}`, data)
            .then((response) => {
                if (action === 'download') {
                    this.downloadPdf(response)
                }

                this.setState({ showSuccessMessage: true })
            })
            .catch((error) => {
                this.setState({ showErrorMessage: true })
                console.warn(error)
            })
    }

    getFormData () {
        return {
            customer_id: this.state.customer_id,
            rating: this.state.rating,
            source_type: this.state.source_type,
            valued_at: this.state.valued_at,
            title: this.state.title,
            content: this.state.description,
            contributors: this.state.selectedUsers,
            due_date: moment(this.state.due_date).format('YYYY-MM-DD'),
            start_date: moment(this.state.start_date).format('YYYY-MM-DD'),
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2
        }
    }

    handleSave () {
        const index = this.props.allTasks.findIndex(task => task.id === this.props.task.id)
        const currentObject = this.props.allTasks[index]
        const data = this.getFormData()

        axios.put(`/api/tasks/${this.props.task.id}`, data)
            .then((response) => {
                this.setState({
                    editMode: false
                })
                currentObject.title = this.state.title
                currentObject.content = this.state.description
                currentObject.contributors = this.state.contributors
                currentObject.due_date = this.state.due_date
                currentObject.start_date = this.state.start_date
                this.props.action(this.props.allTasks)
            })
            .catch((error) => {
                this.setState({
                    err: error.response.data.errors
                })
            })
    }

    handleDelete () {
        this.setState({
            editMode: false
        })
        if (this.props.onDelete) {
            this.props.onDelete(this.props.task)
        }
    }

    handleChange (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })
    }

    getFormForLead (readOnly = false) {
        const objValues = {
            rating: this.state.rating,
            source_type: this.state.source_type,
            valued_at: this.state.valued_at,
            customer_id: this.state.customer_id
        }

        return (
            <React.Fragment>
                <AddLead
                    readOnly={readOnly}
                    updateValue={this.handleChange} task={objValues}
                />
            </React.Fragment>
        )
    }

    handleMultiSelect (e) {
        this.setState({ selectedUsers: Array.from(e.target.selectedOptions, (item) => item.value) })
    }

    buildUserOptions () {
        let userContent = null
        if (!this.props.users) {
            userContent = <option value="">Loading...</option>
        } else {
            userContent = this.props.users.map((user, index) => (
                <option key={index}
                    value={user.id}>{user.first_name + ' ' + user.last_name}</option>
            ))
        }

        return userContent
    }

    render () {
        const sendEmailButton = <DropdownItem className="primary" onClick={() => this.changeStatus('email')}>Send
            Email</DropdownItem>

        const deleteButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('delete')}>Delete</DropdownItem> : null

        const archiveButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('archive')}>Archive</DropdownItem> : null

        const cloneButton =
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_task')}>Clone</DropdownItem>

        const userContent = this.buildUserOptions()
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleChange.bind(this)}
            formFieldsRows={customFields}
        /> : null
        const leadForm = this.props.task_type === 2 ? this.getFormForLead(true) : ''

        const dropdownMenu = <Dropdown isOpen={this.state.dropdownOpen} toggle={this.toggleMenu}>
            <DropdownToggle caret>
                Actions
            </DropdownToggle>

            <DropdownMenu>
                {sendEmailButton}
                {deleteButton}
                {archiveButton}
                {cloneButton}
            </DropdownMenu>
        </Dropdown>

        const successMessage = this.state.showSuccessMessage === true
            ? <SuccessMessage message="Invoice was updated successfully"/> : null
        const errorMessage = this.state.showErrorMessage === true
            ? <ErrorMessage message="Something went wrong"/> : null

        const form = <React.Fragment>
            <Nav tabs>
                <NavItem>
                    <NavLink
                        className={this.state.activeTab === '1' ? 'active' : ''}
                        onClick={() => {
                            this.toggleTab('1')
                        }}>
                        Details
                    </NavLink>
                </NavItem>

                <NavItem>
                    <NavLink
                        className={this.state.activeTab === '2' ? 'active' : ''}
                        onClick={() => {
                            this.toggleTab('2')
                        }}>
                        Times
                    </NavLink>
                </NavItem>
            </Nav>

            <TabContent activeTab={this.state.activeTab}>
                <TabPane tabId="1">

                    {dropdownMenu}
                    {successMessage}
                    {errorMessage}

                    <Card>
                        <CardHeader>Details</CardHeader>
                        <CardBody>
                            <FormGroup>
                                <Label>Title</Label>
                                <Input type="text" name="title"
                                    value={this.state.title}
                                    onChange={this.handleChange}/>
                            </FormGroup>

                            <FormGroup>
                                <Label>Description</Label>
                                <Input type="textarea" name="description"
                                    value={this.state.description}
                                    onChange={this.handleChange}/>
                            </FormGroup>

                            <FormGroup>
                                <Label>Start / End date</Label>
                                <DateRangePicker
                                    startDate={this.state.start_date} // momentPropTypes.momentObj or null,
                                    startDateId="your_unique_start_date_id" // PropTypes.string.isRequired,
                                    endDate={this.state.due_date} // momentPropTypes.momentObj or null,
                                    endDateId="due_date" // PropTypes.string.isRequired,
                                    displayFormat="DD-MM-YYYY"
                                    onDatesChange={({ startDate, endDate }) => this.setState({
                                        start_date: startDate,
                                        due_date: endDate
                                    })} // PropTypes.func.isRequired,
                                    focusedInput={this.state.focusedInput} // PropTypes.oneOf([START_DATE, END_DATE]) or null,
                                    onFocusChange={focusedInput => this.setState({ focusedInput })} // PropTypes.func.isRequired,
                                />
                            </FormGroup>

                            <FormGroup>
                                <Label>Assigned To</Label>
                                <Input multiple
                                    type="select"
                                    value={this.state.selectedUsers}
                                    id="contributors"
                                    name="contributors"
                                    onChange={this.handleMultiSelect}>
                                    {userContent}
                                </Input>
                            </FormGroup>

                            {leadForm}
                            {customForm}
                        </CardBody>
                    </Card>
                </TabPane>

                <TabPane tabId="2">
                    <Card>
                        <CardHeader>Details</CardHeader>
                        <CardBody>
                            <EditTaskTimes task_id={this.props.task.id}/>
                        </CardBody>
                    </Card>

                </TabPane>
            </TabContent>
        </React.Fragment>

        const button = this.props.listView && this.props.listView === true
            ? <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
            : null

        return this.props.modal && this.props.modal === true
            ? <React.Fragment>
                {button}
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Edit Task
                    </ModalHeader>

                    <ModalBody>
                        {form}
                    </ModalBody>
                    <ModalFooter>
                        <Button color="primary" onClick={this.handleSave.bind(this)}>Add</Button>
                        <Button color="secondary" onClick={this.toggle}>Close</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment> : form
    }
}

export default EditTask
