import React from 'react'
import {
    Button,
    Modal,
    ModalHeader,
    ModalBody,
    ModalFooter,
    Input,
    FormGroup,
    Label,
    Card,
    CardBody,
    CardHeader,
    Row,
    Col,
    Dropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem, TabPane
} from 'reactstrap'
import axios from 'axios'
import PropTypes from 'prop-types'
import DropdownDate from '../common/DropdownDate'
import RoleDropdown from '../common/RoleDropdown'
import DepartmentDropdown from '../common/DepartmentDropdown'
import FormBuilder from '../accounts/FormBuilder'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'
import AccountDropdown from '../common/AccountDropdown'

class EditUser extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            loading: false,
            changesMade: false,
            dropdownOpen: false,
            errors: [],
            user: [],
            roles: [],
            selectedRoles: [],
            selectedAccounts: [],
            department: 0,
            message: '',
            custom_value1: '',
            custom_value2: '',
            custom_value3: '',
            custom_value4: ''
        }

        this.defaultValues = {
            year: 'Select Year',
            month: 'Select Month',
            day: 'Select Day'
        }

        this.classes = {
            dateContainer: 'form-row',
            yearContainer: 'col-md-4 mb-3',
            monthContainer: 'col-md-4 mb-3',
            dayContainer: 'col-md-4 mb-3'
        }

        this.initialState = this.state
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleMultiSelect = this.handleMultiSelect.bind(this)
        this.handleAccountMultiSelect = this.handleAccountMultiSelect.bind(this)
        this.setDate = this.setDate.bind(this)
        this.buildGenderDropdown = this.buildGenderDropdown.bind(this)
        this.handleInput = this.handleInput.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
    }

    componentDidMount () {
        this.getUser()
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    getUser () {
        axios.get(`/api/users/edit/${this.props.user_id}`, { headers: { Authorization: `Bearer ${localStorage.getItem('access_token')}` } })
            .then((r) => {
                this.setState({
                    roles: r.data.roles,
                    user: r.data.user,
                    selectedRoles: r.data.selectedIds
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    getFormData () {
        return {
            company_user: this.state.selectedAccounts,
            username: this.state.user.username,
            department: this.state.user.department,
            email: this.state.user.email,
            first_name: this.state.user.first_name,
            last_name: this.state.user.last_name,
            password: this.state.user.password,
            role: this.state.selectedRoles,
            job_description: this.state.user.job_description,
            phone_number: this.state.user.phone_number,
            dob: this.state.user.dob,
            gender: this.state.user.gender,
            custom_value1: this.state.custom_value1,
            custom_value2: this.state.custom_value2,
            custom_value3: this.state.custom_value3,
            custom_value4: this.state.custom_value4
        }
    }

    changeStatus (action) {
        if (!this.props.user.id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/user/${this.props.user.id}/${action}`, data)
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

    handleClick () {
        const data = this.getFormData()

        axios.put(`/api/users/${this.state.user.id}`, data)
            .then((response) => {
                this.initialState = this.state
                const index = this.props.users.findIndex(user => parseInt(user.id) === this.props.user_id)
                this.props.users[index] = this.state.user
                this.props.action(this.props.users)
                this.setState({ message: '' })
                this.toggle()
            })
            .catch((error) => {
                if (error.response.data.errors) {
                    this.setState({
                        errors: error.response.data.errors
                    })
                } else {
                    this.setState({ message: error.response.data })
                }
            })
    }

    renderErrorFor (field) {
        if (this.hasErrorFor(field)) {
            return (
                <span className='invalid-feedback'>
                    <strong>{this.state.errors[field][0]}</strong>
                </span>
            )
        }
    }

    hasErrorFor (field) {
        return !!this.state.errors[field]
    }

    setValues (values) {
        this.setState({ user: { ...this.state.user, ...values } })
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })
    }

    handleMultiSelect (e) {
        this.setState({ selectedRoles: Array.from(e.target.selectedOptions, (item) => item.value) })
    }

    handleAccountMultiSelect (e) {
        this.setState({ selectedAccounts: Array.from(e.target.selectedOptions, (item) => item.value) }, () => console.log('accounts', this.state.selectedAccounts))
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

    setDate (date) {
        this.setValues({ dob: date })
    }

    buildGenderDropdown () {
        const arrOptions = ['male', 'female']

        const options = arrOptions.map(option => {
            return <option key={option} value={option}>{option}</option>
        })

        return (
            <FormGroup>
                <Label for="gender">Gender(*):</Label>
                <Input className={this.hasErrorFor('gender') ? 'is-invalid' : ''}
                    value={this.state.user.gender}
                    type="select"
                    name="gender"
                    onChange={this.handleInput.bind(this)}>
                    <option value="">Select gender</option>
                    {options}
                </Input>
                {this.renderErrorFor('gender')}
            </FormGroup>
        )
    }

    render () {
        const deleteButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('delete')}>Delete</DropdownItem> : null

        const archiveButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('archive')}>Archive</DropdownItem> : null

        const cloneButton =
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_task')}>Clone</DropdownItem>

        const genderList = this.buildGenderDropdown()
        const { message } = this.state
        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null

        const dropdownMenu = <Dropdown isOpen={this.state.dropdownOpen} toggle={this.toggleMenu}>
            <DropdownToggle caret>
                Actions
            </DropdownToggle>

            <DropdownMenu>
                {deleteButton}
                {archiveButton}
                {cloneButton}
            </DropdownMenu>
        </Dropdown>

        const successMessage = this.state.showSuccessMessage === true
            ? <SuccessMessage message="Invoice was updated successfully"/> : null
        const errorMessage = this.state.showErrorMessage === true
            ? <ErrorMessage message="Something went wrong"/> : null

        return (
            <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal size="lg" isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Edit User
                    </ModalHeader>
                    <ModalBody>

                        {message && <div className="alert alert-danger" role="alert">
                            {message}
                        </div>}

                        {dropdownMenu}
                        {successMessage}
                        {errorMessage}

                        <Card>
                            <CardHeader>User</CardHeader>
                            <CardBody>
                                <Row form>
                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="username">Username(*):</Label>
                                            <Input className={this.hasErrorFor('username') ? 'is-invalid' : ''}
                                                placeholder="Username"
                                                type="text"
                                                name="username" defaultValue={this.state.user.username}
                                                onChange={this.handleInput.bind(this)}/>
                                            <small className="form-text text-muted">Your username must be "firstname"."lastname"
                                                eg
                                                joe.bloggs.
                                            </small>
                                            {this.renderErrorFor('username')}
                                        </FormGroup>
                                    </Col>

                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="email">Email(*):</Label>
                                            <Input className={this.hasErrorFor('email') ? 'is-invalid' : ''} type="email"
                                                name="email"
                                                defaultValue={this.state.user.email} onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('email')}
                                        </FormGroup>
                                    </Col>
                                </Row>

                                <Row form>
                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="first_name">First Name(*):</Label>
                                            <Input className={this.hasErrorFor('first_name') ? 'is-invalid' : ''} type="text"
                                                name="first_name" defaultValue={this.state.user.first_name}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('first_name')}
                                        </FormGroup>
                                    </Col>

                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="last_name">Last Name(*):</Label>
                                            <Input className={this.hasErrorFor('last_name') ? 'is-invalid' : ''} type="text"
                                                name="last_name" defaultValue={this.state.user.last_name}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('last_name')}
                                        </FormGroup>
                                    </Col>
                                </Row>

                                <Row form>
                                    <Col md={6}>
                                        {genderList}
                                    </Col>

                                    <Col md={6}>
                                        <DropdownDate selectedDate={this.state.user.dob} classes={this.classes}
                                            defaultValues={this.defaultValues} onDateChange={this.setDate}/>
                                    </Col>
                                </Row>

                                <Row form>
                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="phone_number">Phone Number:</Label>
                                            <Input className={this.hasErrorFor('phone_number') ? 'is-invalid' : ''}
                                                value={this.state.user.phone_number}
                                                type="tel"
                                                name="phone_number"
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('phone_number')}
                                        </FormGroup>
                                    </Col>

                                    <Col md={6}>
                                        <FormGroup>
                                            <Label for="password">Password:</Label>
                                            <Input className={this.hasErrorFor('password') ? 'is-invalid' : ''} type="password"
                                                name="password" defaultValue={this.state.user.password}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('password')}
                                        </FormGroup>
                                    </Col>
                                </Row>
                            </CardBody>
                        </Card>

                        {customForm}

                        <Card>
                            <CardHeader>Job Details</CardHeader>
                            <CardBody>

                                <FormGroup>
                                    <Label for="password">Job Description:</Label>
                                    <Input className={this.hasErrorFor('job_description') ? 'is-invalid' : ''}
                                        type="text"
                                        value={this.state.user.job_description}
                                        placeholder="Job Description"
                                        name="job_description"
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('job_description')}
                                </FormGroup>

                                <Row form>
                                    <Col md={6}>
                                        <Label for="password">Department:</Label>
                                        <DepartmentDropdown
                                            departments={this.props.departments && this.props.departments.length ? this.props.departments : ''}
                                            department={this.state.user.department}
                                            errors={this.state.errors}
                                            handleInputChanges={this.handleInput}
                                        />
                                    </Col>

                                    <Col md={6}>
                                        <RoleDropdown
                                            multiple={true}
                                            name="role"
                                            errors={this.state.errors}
                                            handleInputChanges={this.handleMultiSelect}
                                        />
                                    </Col>

                                      <Col>
                                        <AccountDropdown handleInputChanges={this.handleAccountMultiSelect}
                                            accounts={this.props.accounts}/>
                                    </Col>
                                </Row>
                            </CardBody>
                        </Card>
                    </ModalBody>

                    <ModalFooter>
                        <Button color="primary" onClick={this.handleClick.bind(this)}>Update</Button>
                        <Button color="secondary" onClick={this.toggle}>Close</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}

export default EditUser

EditUser.propTypes = {
    user: PropTypes.object,
    users: PropTypes.array,
    action: PropTypes.func
}
