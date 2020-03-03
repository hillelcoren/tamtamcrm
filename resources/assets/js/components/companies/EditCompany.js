import React from 'react'
import {
    Button,
    Modal,
    ModalHeader,
    ModalBody,
    ModalFooter,
    CustomInput,
    Input,
    FormGroup,
    Label,
    Card,
    CardBody,
    CardHeader,
    Nav,
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
import CountryDropdown from '../common/CountryDropdown'
import CurrencyDropdown from '../common/CurrencyDropdown'
import IndustryDropdown from '../common/IndustryDropdown'
import UserDropdown from '../common/UserDropdown'
import FormBuilder from '../accounts/FormBuilder'
import Contact from '../common/Contact'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'

class EditCompany extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            loading: false,
            dropdownOpen: false,
            changesMade: false,
            showSuccessMessage: false,
            showErrorMessage: false,
            errors: [],
            contacts: [],
            name: this.props.brand.name,
            website: this.props.brand.website,
            phone_number: this.props.brand.phone_number,
            email: this.props.brand.email,
            address_1: this.props.brand.address_1,
            address_2: this.props.brand.address_2,
            town: this.props.brand.town,
            city: this.props.brand.city,
            country_id: this.props.brand.country_id,
            currency_id: this.props.brand.currency_id,
            industry_id: this.props.brand.industry_id,
            postcode: this.props.brand.postcode,
            id: this.props.brand.id,
            company_logo: null,
            assigned_user_id: this.props.brand.assigned_user_id,
            custom_value1: this.props.brand.custom_value1,
            custom_value2: this.props.brand.custom_value2,
            custom_value3: this.props.brand.custom_value3,
            custom_value4: this.props.brand.custom_value4,
            notes: this.props.brand.notes,
            activeTab: '1'
        }

        this.initialState = this.state
        this.updateContacts = this.updateContacts.bind(this)
        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleMultiSelect = this.handleMultiSelect.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
    }

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    handleFileChange (e) {
        this.setState({
            [e.target.name]: e.target.files[0]
        })
    }

    updateContacts (contacts) {
        this.setState({ contacts: contacts })
    }

    toggleTab (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    handleMultiSelect (e) {
        this.setState({ selectedUsers: Array.from(e.target.selectedOptions, (item) => item.value) })
    }

    hasErrorFor (field) {
        return !!this.state.errors[field]
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

    getFormData () {
        const formData = new FormData()
        formData.append('company_logo', this.state.company_logo)
        formData.append('name', this.state.name)
        formData.append('website', this.state.website)
        formData.append('phone_number', this.state.phone_number)
        formData.append('email', this.state.email)
        formData.append('address_1', this.state.address_1)
        formData.append('address_2', this.state.address_2)
        formData.append('town', this.state.town)
        formData.append('city', this.state.city)
        formData.append('postcode', this.state.postcode)
        formData.append('country_id', this.state.country_id)
        formData.append('currency_id', this.state.currency_id)
        formData.append('industry_id', this.state.industry_id)
        formData.append('assigned_user_id', this.state.assigned_user_id)
        formData.append('custom_value1', this.state.custom_value1)
        formData.append('custom_value2', this.state.custom_value2)
        formData.append('custom_value3', this.state.custom_value3)
        formData.append('custom_value4', this.state.custom_value4)
        formData.append('notes', this.state.notes)
        formData.append('contacts', JSON.stringify(this.state.contacts))
        formData.append('_method', 'PUT')

        return formData
    }

    changeStatus (action) {
        if (!this.state.id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/company/${this.state.id}/${action}`, data)
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
        const formData = this.getFormData()

        axios.post(`/api/companies/${this.state.id}`, formData)
            .then((response) => {
                this.toggle()
                const index = this.props.brands.findIndex(company => parseInt(company.id) === this.state.id)
                this.props.brands[index] = response.data
                this.props.action(this.props.brands)
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
            })
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

    render () {
        const sendEmailButton = <DropdownItem className="primary" onClick={() => this.changeStatus('email')}>Send
            Email</DropdownItem>

        const deleteButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('delete')}>Delete</DropdownItem> : null

        const archiveButton = this.state.status_id === 1
            ? <DropdownItem className="primary" onClick={() => this.changeStatus('archive')}>Archive</DropdownItem> : null

        const cloneButton =
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_company')}>Clone</DropdownItem>

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

        const customFields = this.props.custom_fields ? this.props.custom_fields : []
        const customForm = customFields && customFields.length ? <FormBuilder
            handleChange={this.handleInput.bind(this)}
            formFieldsRows={customFields}
        /> : null
        return (
            <React.Fragment>
                <DropdownItem onClick={this.toggle}><i className="fa fa-edit"/>Edit</DropdownItem>
                <Modal isOpen={this.state.modal} toggle={this.toggle} className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>
                        Edit Brand
                    </ModalHeader>
                    <ModalBody>

                        {dropdownMenu}
                        {successMessage}
                        {errorMessage}

                        <Nav tabs>
                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '1' ? 'active' : ''}
                                    onClick={() => {
                                        this.toggleTab('1')
                                    }}>
                                    Company
                                </NavLink>
                            </NavItem>
                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '2' ? 'active' : ''}
                                    onClick={() => {
                                        this.toggleTab('2')
                                    }}>
                                    Contacts
                                </NavLink>
                            </NavItem>

                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '3' ? 'active' : ''}
                                    onClick={() => {
                                        this.toggleTab('3')
                                    }}>
                                    Address
                                </NavLink>
                            </NavItem>

                            <NavItem>
                                <NavLink
                                    className={this.state.activeTab === '4' ? 'active' : ''}
                                    onClick={() => {
                                        this.toggleTab('4')
                                    }}>
                                    Settings
                                </NavLink>
                            </NavItem>
                        </Nav>
                        <TabContent activeTab={this.state.activeTab}>
                            <TabPane tabId="1">
                                <Card>
                                    <CardHeader>Product</CardHeader>
                                    <CardBody>
                                        <FormGroup>
                                            <Label for="name">Name(*):</Label>
                                            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''}
                                                type="text"
                                                placeholder="Name"
                                                name="name"
                                                value={this.state.name}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('name')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="website">Website(*):</Label>
                                            <Input className={this.hasErrorFor('website') ? 'is-invalid' : ''}
                                                type="text"
                                                name="website"
                                                placeholder="Website"
                                                value={this.state.website}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('website')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="phone_number">Phone Number(*):</Label>
                                            <Input className={this.hasErrorFor('phone_number') ? 'is-invalid' : ''}
                                                placeholder="Phone Number"
                                                type="tel"
                                                name="phone_number"
                                                value={this.state.phone_number}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('phone_number')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="email">Email(*):</Label>
                                            <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''}
                                                placeholder="Email"
                                                type="email"
                                                name="email"
                                                value={this.state.email}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('email')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label>Logo</Label>
                                            <CustomInput className="mt-4 mb-4"
                                                onChange={this.handleFileChange.bind(this)} type="file"
                                                id="company_logo" name="company_logo"
                                                label="Logo"/>
                                        </FormGroup>

                                        {customForm}
                                    </CardBody>
                                </Card>
                            </TabPane>

                            <TabPane tabId="2">
                                <Card>
                                    <CardHeader>
                                        Contacts
                                    </CardHeader>

                                    <CardBody>
                                        <Contact contacts={this.props.brand.contacts} onChange={this.updateContacts}/>
                                    </CardBody>
                                </Card>
                            </TabPane>

                            <TabPane tabId="3">
                                <Card>
                                    <CardHeader>Address</CardHeader>
                                    <CardBody>
                                        <FormGroup>
                                            <Label for="address_1">Address(*):</Label>
                                            <Input className={this.hasErrorFor('address_1') ? 'is-invalid' : ''}
                                                placeholder="Address"
                                                type="text"
                                                name="address_1"
                                                value={this.state.address_1}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('address_1')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="address_2">Address 2:</Label>
                                            <Input className={this.hasErrorFor('address_2') ? 'is-invalid' : ''}
                                                placeholder="Address"
                                                type="text"
                                                name="address_2"
                                                value={this.state.address_2}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('address_2')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="town">Town(*):</Label>
                                            <Input className={this.hasErrorFor('town') ? 'is-invalid' : ''}
                                                placeholder="Town"
                                                type="text"
                                                name="town"
                                                value={this.state.town}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('town')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="city">City(*):</Label>
                                            <Input className={this.hasErrorFor('city') ? 'is-invalid' : ''}
                                                placeholder="City"
                                                type="text"
                                                name="city"
                                                value={this.state.city}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('city')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="postcode">Postcode(*):</Label>
                                            <Input className={this.hasErrorFor('postcode') ? 'is-invalid' : ''}
                                                placeholder="Postcode"
                                                type="text"
                                                name="postcode"
                                                value={this.state.postcode}
                                                onChange={this.handleInput.bind(this)}/>
                                            {this.renderErrorFor('postcode')}
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="postcode">Country(*):</Label>
                                            <CountryDropdown
                                                country={this.state.country_id}
                                                errors={this.state.errors}
                                                handleInputChanges={this.handleInput.bind(this)}
                                            />
                                        </FormGroup>
                                    </CardBody>
                                </Card>
                            </TabPane>

                            <TabPane tabId="4">
                                <Card>
                                    <CardHeader>Settings</CardHeader>
                                    <CardBody>
                                        <FormGroup>
                                            <Label for="postcode">Currency(*):</Label>
                                            <CurrencyDropdown
                                                currency_id={this.state.currency_id}
                                                errors={this.state.errors}
                                                handleInputChanges={this.handleInput.bind(this)}
                                            />
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="postcode">Industry:</Label>
                                            <IndustryDropdown
                                                industry_id={this.state.industry_id}
                                                errors={this.state.errors}
                                                handleInputChanges={this.handleInput.bind(this)}
                                            />
                                        </FormGroup>

                                        <FormGroup>
                                            <Label for="postcode">Users:</Label>
                                            <UserDropdown
                                                user={this.state.assigned_user_id}
                                                name="assigned_user_id"
                                                errors={this.state.errors}
                                                handleInputChanges={this.handleInput.bind(this)}
                                            />
                                        </FormGroup>
                                    </CardBody>
                                </Card>

                                <Card>
                                    <CardHeader>Notes</CardHeader>
                                    <CardBody>

                                        <FormGroup>
                                            <Label for="postcode">Notes:</Label>
                                            <Input
                                                value={this.state.notes}
                                                type='textarea'
                                                name="notes"
                                                errors={this.state.errors}
                                                onChange={this.handleInput.bind(this)}
                                            />
                                        </FormGroup>
                                    </CardBody>
                                </Card>
                            </TabPane>
                        </TabContent>

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

export default EditCompany
