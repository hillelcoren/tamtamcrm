import React, { Component } from 'react'
import axios from 'axios'
import EditProduct from './EditProduct'
import AddProduct from './AddProduct'
import DataTable from '../common/DataTable'
import {
    FormGroup, Input, Card, CardBody, Col, Row
} from 'reactstrap'
import CategoryDropdown from '../common/CategoryDropdown'
import CompanyDropdown from '../common/CompanyDropdown'
import RestoreModal from '../common/RestoreModal'
import DeleteModal from '../common/DeleteModal'
import DisplayColumns from '../common/DisplayColumns'
import ActionsMenu from '../common/ActionsMenu'
import TableSearch from '../common/TableSearch'
import FilterTile from '../common/FilterTile'
import ViewEntity from '../common/ViewEntity'
import DateFilter from '../common/DateFilter'
import CsvImporter from '../common/CsvImporter'
import BulkActionDropdown from '../common/BulkActionDropdown'

export default class ProductList extends Component {
    constructor (props) {
        super(props)
        this.state = {
            per_page: 5,
            view: {
                viewMode: false,
                viewedId: null,
                title: null
            },
            products: [],
            companies: [],
            cachedData: [],
            categories: [],
            custom_fields: [],
            dropdownButtonActions: ['download'],
            bulk: [],
            filters: {
                status: 'active',
                category_id: '',
                company_id: '',
                searchText: '',
                start_date: '',
                end_date: ''
            },
            ignoredColumns: [
                'deleted_at',
                'created_at',
                'cover',
                'images',
                'company_id',
                'category_ids',
                'status',
                'range_from',
                'range_to',
                'payable_months',
                'minimum_downpayment',
                'number_of_years',
                'assigned_user_id',
                'user_id',
                'notes',
                'cost',
                'quantity',
                'interest_rate',
                'price',
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4'
            ],
            showRestoreButton: false
        }

        this.addProductToState = this.addProductToState.bind(this)
        this.userList = this.userList.bind(this)
        this.filterProducts = this.filterProducts.bind(this)
        this.deleteProduct = this.deleteProduct.bind(this)
        this.updateIgnoredColumns = this.updateIgnoredColumns.bind(this)
        this.getFilters = this.getFilters.bind(this)
        this.toggleViewedEntity = this.toggleViewedEntity.bind(this)
        this.onChangeBulk = this.onChangeBulk.bind(this)
        this.getCompanies = this.getCompanies.bind(this)
        this.saveBulk = this.saveBulk.bind(this)
    }

    componentDidMount () {
        this.getCompanies()
        this.getCategories()
        this.getCustomFields()
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
        axios.post('/api/product/bulk', { ids: this.state.bulk, action: action }).then(function (response) {
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

    addProductToState (products) {
        const cachedData = !this.state.cachedData.length ? products : this.state.cachedData
        this.setState({
            products: products,
            cachedData: cachedData
        })
    }

    filterProducts (event) {
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
        const { categories } = this.state
        const { status, searchText, category_id, company_id, start_date, end_date } = this.state.filters
        const columnFilter = this.state.products.length
            ? <DisplayColumns onChange2={this.updateIgnoredColumns} columns={Object.keys(this.state.products[0])}
                ignored_columns={this.state.ignoredColumns}/> : null
        return (
            <Row form>
                <Col md={2}>
                    <TableSearch onChange={this.filterProducts}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <Input type='select'
                            onChange={this.filterProducts}
                            id="status"
                            name="status"
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
                        handleInputChanges={this.filterProducts}
                        companies={this.state.companies}
                        name="company_id"
                    />
                </Col>

                <Col md={3}>
                    <CategoryDropdown
                        name="category_id"
                        renderErrorFor={this.renderErrorFor}
                        handleInputChanges={this.filterProducts}
                        categories={categories}
                    />
                </Col>

                <Col>
                    <BulkActionDropdown
                        dropdownButtonActions={this.state.dropdownButtonActions}
                        saveBulk={this.saveBulk}/>
                </Col>

                <Col>
                    <CsvImporter filename="products.csv"
                        url={`/api/products?search_term=${searchText}&status=${status}&category_id=${category_id}&company_id=${company_id}&start_date=${start_date}&end_date=${end_date}&page=1&per_page=5000`}/>
                </Col>

                <Col md={2}>
                    <FormGroup>
                        <DateFilter onChange={this.filterProducts} update={this.addProductToState}
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

    getCustomFields () {
        axios.get('api/accounts/fields/Product')
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

    getCategories () {
        axios.get('/api/categories')
            .then((r) => {
                this.setState({
                    categories: r.data
                })
            })
            .catch((e) => {
                console.error(e)
            })
    }

    userList () {
        const { products, custom_fields, companies, categories, ignoredColumns } = this.state

        if (products && products.length) {
            return products.map(product => {
                const restoreButton = product.deleted_at
                    ? <RestoreModal id={product.id} entities={products} updateState={this.addProductToState}
                        url={`/api/products/restore/${product.id}`}/> : null
                const deleteButton = !product.deleted_at
                    ? <DeleteModal deleteFunction={this.deleteProduct} id={product.id}/> : null
                const editButton = !product.deleted_at ? <EditProduct
                    custom_fields={custom_fields}
                    companies={companies}
                    categories={categories}
                    product={product}
                    products={products}
                    action={this.addProductToState}
                /> : null

                const archiveButton = !product.deleted_at
                    ? <DeleteModal archive={true} deleteFunction={this.deleteProduct} id={product.id}/> : null

                const columnList = Object.keys(product).filter(key => {
                    return ignoredColumns && !ignoredColumns.includes(key)
                }).map(key => {
                    return <td onClick={() => this.toggleViewedEntity(product, product.name)} data-label={key}
                        key={key}>{product[key]}</td>
                })

                return <tr key={product.id}>
                    <td>
                        <Input value={product.id} type="checkbox" onChange={this.onChangeBulk} />
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

    deleteProduct (id, archive = true) {
        const self = this
        const url = archive === true ? `/api/products/archive/${id}` : `/api/products/${id}`
        axios.delete(url)
            .then(function (response) {
                const arrProducts = [...self.state.products]
                const index = arrProducts.findIndex(product => product.id === id)
                arrProducts.splice(index, 1)
                self.addProductToState(arrProducts)
            })
            .catch(function (error) {
                console.log(error)
            })
    }

    render () {
        const { products, custom_fields, companies, categories, view } = this.state
        const { status, searchText, category_id, company_id, start_date, end_date } = this.state.filters
        const fetchUrl = `/api/products?search_term=${searchText}&status=${status}&category_id=${category_id}&company_id=${company_id}&start_date=${start_date}&end_date=${end_date}`
        const filters = categories.length && companies.length ? this.getFilters() : 'Loading filters'
        const addButton = companies.length && categories.length ? <AddProduct
            custom_fields={custom_fields}
            companies={companies}
            categories={categories}
            products={products}
            action={this.addProductToState}
        /> : null

        return (
            <div className="data-table">

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
                            updateState={this.addProductToState}
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
