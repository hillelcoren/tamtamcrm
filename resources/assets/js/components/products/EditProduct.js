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
    CustomInput,
    Card,
    CardBody,
    CardHeader,
    Dropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem
} from 'reactstrap'
import axios from 'axios'
import PropTypes from 'prop-types'
import ProductAttribute from './ProductAttribute'
import CompanyDropdown from '../common/CompanyDropdown'
import CategoryDropdown from '../common/CategoryDropdown'
import UserDropdown from '../common/UserDropdown'
import FormBuilder from '../accounts/FormBuilder'
import SuccessMessage from '../common/SucessMessage'
import ErrorMessage from '../common/ErrorMessage'

class EditProduct extends React.Component {
    constructor (props) {
        super(props)

        this.productAttributes = {
            range_from: this.props.product.range_from ? this.props.product.range_from : 0,
            range_to: this.props.product.range_to ? this.props.product.range_to : 0,
            payable_months: this.props.product.payable_months ? this.props.product.payable_months : 12,
            minimum_downpayment: this.props.product.minimum_downpayment ? this.props.product.minimum_downpayment : 0,
            number_of_years: this.props.product.number_of_years ? this.props.product.number_of_years : 0,
            interest_rate: this.props.product.interest_rate ? this.props.product.interest_rate : 0
        }

        this.state = {
            modal: false,
            loading: false,
            changesMade: false,
            dropdownOpen: false,
            showSuccessMessage: false,
            showErrorMessage: false,
            errors: [],
            name: this.props.product.name,
            description: this.props.product.description,
            price: this.props.product.price,
            cost: this.props.product.cost,
            quantity: this.props.product.quantity,
            sku: this.props.product.sku,
            images: this.props.product.images,
            id: this.props.product.id,
            categories: [],
            selectedCategories: this.props.product.category_ids ? this.props.product.category_ids : [],
            company_id: this.props.product.company_id,
            assigned_user_id: this.props.product.assigned_user_id,
            notes: this.props.product.notes,
            custom_value1: this.props.product.custom_value1,
            custom_value2: this.props.product.custom_value2,
            custom_value3: this.props.product.custom_value3,
            custom_value4: this.props.product.custom_value4
        }

        this.initialState = this.state
        this.state = { ...this.state, ...this.productAttributes }

        this.toggle = this.toggle.bind(this)
        this.hasErrorFor = this.hasErrorFor.bind(this)
        this.renderErrorFor = this.renderErrorFor.bind(this)
        this.handleMultiSelect = this.handleMultiSelect.bind(this)
        this.handleInput = this.handleInput.bind(this)
        this.deleteImage = this.deleteImage.bind(this)
        this.toggleMenu = this.toggleMenu.bind(this)
        this.changeStatus = this.changeStatus.bind(this)
    }

    toggleMenu (event) {
        this.setState({
            dropdownOpen: !this.state.dropdownOpen
        })
    }

    getFormData () {
        const formData = new FormData()
        formData.append('cover', this.state.cover)

        if (this.state.image && this.state.image.length) {
            for (let x = 0; x < this.state.image.length; x++) {
                formData.append('image[]', this.state.image[x])
            }
        }

        formData.append('name', this.state.name)
        formData.append('assigned_user_id', this.state.assigned_user_id)
        formData.append('notes', this.state.notes)
        formData.append('description', this.state.description)
        formData.append('quantity', this.state.quantity)
        formData.append('price', this.state.price)
        formData.append('cost', this.state.cost)
        formData.append('sku', this.state.sku)
        formData.append('company_id', this.state.company_id)
        formData.append('category', this.state.selectedCategories)
        formData.append('range_from', this.state.range_from)
        formData.append('range_to', this.state.range_to)
        formData.append('payable_months', this.state.payable_months)
        formData.append('number_of_years', this.state.number_of_years)
        formData.append('minimum_downpayment', this.state.minimum_downpayment)
        formData.append('interest_rate', this.state.interest_rate)
        formData.append('_method', 'PUT')

        return formData
    }

    changeStatus (action) {
        if (!this.state.id) {
            return false
        }

        const data = this.getFormData()
        axios.post(`/api/product/${this.state.id}/${action}`, data)
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

        axios.post(`/api/products/${this.state.id}`, formData)
            .then((response) => {
                this.toggle()
                const index = this.props.products.findIndex(product => parseInt(product.id) === this.state.id)
                this.props.products[index] = response.data
                this.props.action(this.props.products)
            })
            .catch((error) => {
                this.setState({
                    errors: error.response.data.errors
                })
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

    handleInput (e) {
        this.setState({
            [e.target.name]: e.target.value,
            changesMade: true
        })
    }

    handleMultiSelect (e) {
        this.setState({ selectedCategories: Array.from(e.target.selectedOptions, (item) => item.value) })
    }

    handleFileChange (e) {
        this.setState({
            [e.target.name]: e.target.files[0]
        })
    }

    onChangeHandler (e) {
        const files = e.target.files

        console.log('files', files)

        // if return true allow to setState
        this.setState({
            [e.target.name]: e.target.files
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

    deleteImage (e) {
        const src = e.target.getAttribute('data-src')

        axios.post('/api/products/removeImages', {
            product: this.state.id,
            image: src
        })
            .then(function (response) {
                // const arrProducts = [...self.state.products]
                // const index = arrProducts.findIndex(product => product.id === id)
                // arrProducts.splice(index, 1)
                // self.addProductToState(arrProducts)
            })
            .catch(function (error) {
                console.log(error)
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
            <DropdownItem className="primary" onClick={() => this.changeStatus('clone_to_product')}>Clone</DropdownItem>

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
                        Edit Product
                    </ModalHeader>
                    <ModalBody>

                        {dropdownMenu}
                        {successMessage}
                        {errorMessage}

                        <Card>
                            <CardHeader>Product</CardHeader>
                            <CardBody>

                                <FormGroup>
                                    <Label for="name">Name(*):</Label>
                                    <Input className={this.hasErrorFor('name') ? 'is-invalid' : ''}
                                        type="text"
                                        name="name"
                                        defaultValue={this.state.name}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('name')}
                                </FormGroup>

                                <FormGroup>
                                    <Label for="email">Quantity:</Label>
                                    <Input className={this.hasErrorFor('quantity') ? 'is-invalid' : ''}
                                        type="text"
                                        name="quantity"
                                        defaultValue={this.state.quantity}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('quantity')}
                                </FormGroup>

                                <FormGroup>
                                    <Label for="email">Description:</Label>
                                    <Input className={this.hasErrorFor('description') ? 'is-invalid' : ''}
                                        type="textarea"
                                        name="description"
                                        defaultValue={this.state.description}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('description')}
                                </FormGroup>

                                <FormGroup>
                                    <Label for="sku">Sku(*):</Label>
                                    <Input className={this.hasErrorFor('sku') ? 'is-invalid' : ''}
                                        type="text"
                                        name="sku"
                                        defaultValue={this.state.sku}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('sku')}
                                </FormGroup>

                                <CompanyDropdown
                                    name="company_id"
                                    company_id={this.state.company_id}
                                    errors={this.state.errors}
                                    handleInputChanges={this.handleInput}
                                    companies={this.props.companies}
                                />

                                <CategoryDropdown
                                    multiple={true}
                                    name="category"
                                    category={this.state.selectedCategories}
                                    errors={this.state.errors}
                                    handleInputChanges={this.handleMultiSelect}
                                    categories={this.props.categories}
                                />

                                <FormGroup>
                                    <Label for="postcode">Users:</Label>
                                    <UserDropdown
                                        user={this.state.assigned_user_id}
                                        name="assigned_user_id"
                                        errors={this.state.errors}
                                        handleInputChanges={this.handleInput.bind(this)}
                                    />
                                </FormGroup>

                                {customForm}

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

                        <Card>
                            <CardHeader>Prices</CardHeader>
                            <CardBody>
                                <FormGroup>
                                    <Label for="price">Price(*):</Label>
                                    <Input className={this.hasErrorFor('price') ? 'is-invalid' : ''}
                                        type="text"
                                        name="price"
                                        defaultValue={this.state.price}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('price')}
                                </FormGroup>

                                <FormGroup>
                                    <Label for="price">Cost:</Label>
                                    <Input className={this.hasErrorFor('cost') ? 'is-invalid' : ''}
                                        type="text"
                                        name="cost"
                                        defaultValue={this.state.cost}
                                        onChange={this.handleInput.bind(this)}/>
                                    {this.renderErrorFor('cost')}
                                </FormGroup>
                            </CardBody>
                        </Card>

                        <Card>
                            <CardHeader>Images</CardHeader>
                            <CardBody>

                                <FormGroup>
                                    <div className="col-md-3">
                                        <div className="row">
                                            <img src={`/storage/${this.props.product.cover}`} alt=""
                                                className="img-responsive img-thumbnail"/>
                                        </div>
                                    </div>
                                </FormGroup>

                                <FormGroup>
                                    {
                                        this.state.images && this.state.images.map((image, index) => {
                                            return (<div key={index} className="col-md-3">
                                                <div className="row">
                                                    <img src={`/storage/${image.src}`} alt=""
                                                        className="img-responsive img-thumbnail"/>
                                                    <br/> <br/>
                                                    <Button data-src={image.src} color="danger"
                                                        onClick={this.deleteImage.bind(this)}>Remove</Button>
                                                    <br/>
                                                </div>
                                            </div>)
                                        })
                                    }

                                </FormGroup>

                                <FormGroup>
                                    <Label>Cover Image</Label>
                                    <CustomInput onChange={this.handleFileChange.bind(this)} type="file" id="cover"
                                        name="cover"
                                        label="Cover!"/>
                                </FormGroup>

                                <FormGroup>
                                    <Label>Thumbnails</Label>
                                    <Input onChange={this.onChangeHandler.bind(this)} multiple type="file" id="image"
                                        name="image"
                                        label="Thumbnail!"/>
                                </FormGroup>
                            </CardBody>
                        </Card>

                        <Card>
                            <CardHeader>Attributes</CardHeader>
                            <CardBody>

                                <ProductAttribute values={this.productAttributes} product={this.props.product}
                                    onChange={this.handleInput}/>
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

export default EditProduct

EditProduct.propTypes = {
    product: PropTypes.object,
    products: PropTypes.array,
    action: PropTypes.func
}
