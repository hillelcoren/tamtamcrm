import React, { Component } from 'react'
import {
    Row, Col, Nav,
    NavItem,
    NavLink,
    TabContent, TabPane,
    Button,
    ButtonGroup,
    ButtonToolbar,
    Card,
    CardBody,
    CardTitle,
    CardFooter,
    Progress
} from 'reactstrap'
import { CardModule } from './common/Card.jsx'
import ReactEcharts from 'echarts-for-react'
import { StatsCard } from './common/StatsCard.jsx'
import axios from 'axios'
import MessageContainer from './activity/MessageContainer'
import Line from 'react-chartjs-2'
import moment from 'moment'
import { CustomTooltips } from '@coreui/coreui-plugin-chartjs-custom-tooltips'
import {
    getStyle,
    hexToRgba
} from '@coreui/coreui/dist/js/coreui-utilities'
import MonthPicker from './common/MonthPicker'

const brandPrimary = getStyle('--primary')
const brandSuccess = getStyle('--success')
const brandInfo = getStyle('--info')
const brandWarning = getStyle('--warning')
const brandDanger = getStyle('--danger')

function calculateAverage (array) {
    if (!array.length) {
        return 0
    }

    return Math.round(array.reduce((a, b) => (a + b)) / array.length * 100 + Number.EPSILON) / 100
}

function calculateTotals (array) {
    return array.reduce((a, b) => a + b, 0)
}

function getAverages (array) {
    console.log('array', array)
    const avg = calculateAverage(array)
    const totals = calculateTotals(array)
    const pct = calculatePercentage(avg, totals)

    return {
        avg: Math.round(avg),
        value: totals,
        pct: Math.round(pct)
    }
}

function calculatePercentage (number1, number2) {
    if (number1 <= 0 || number2 <= 0) {
        return 0
    }

    return Math.floor((number1 / number2) * 100)
}

function filterByDate (startDate, endDate, array) {
    startDate = new Date(startDate)
    endDate = new Date(endDate)

    // return matches for date range
    return array.filter(function (a) {
        const date = new Date(a.created_at)
        return date >= startDate && date <= endDate
    })
}

function removeNullValues (array, column) {
    return array.filter(e => e[column] !== null && e[column] !== '')
}

function orderByDate (array, dir) {
    return array.sort(function (a, b) {
        const dateA = new Date(a.created_at)
        const dateB = new Date(b.created_at)

        return dir === 'asc' ? dateA - dateB : dateB - dateA // sort by
        // date
        // ascending
    })
}

function groupByStatus (array, status, compare_column) {
    return array.filter(e => e[compare_column] === status)
}

function groupDataByDate (array, column, status, compare_column) {
    return array.reduce(function (m, d) {
        if (status !== null && d[compare_column] !== status) {
            return m
        }

        const date = moment(d.created_at).format('DD')

        if (!m[date]) {
            m[date] = parseFloat(d[column])
            return m
        }
        m[date] += parseFloat(d[column])
        return m
    }, {})
}

function formatData (myData, status, startDate, endDate, column, compare_column, doGrouping = true, dir = 'asc') {
    if (!myData.length) {
        return null
    }

    // sort by date
    myData = orderByDate(myData, dir)

    // get data for specified date range
    let filteredData = filterByDate(startDate, endDate, myData)

    if (!filteredData.length) {
        return null
    }

    // Calculate the sums and group data (while tracking count)
    if (doGrouping) {
        filteredData = groupDataByDate(filteredData, column, status, compare_column)
    } else {
        filteredData = groupByStatus(myData, status, compare_column)
    }

    const avgs = Object.keys(filteredData).length ? getAverages(Object.values(filteredData)) : {
        avg: 0,
        value: 0,
        pct: 0
    }

    return { ...{ data: filteredData }, ...avgs }
}

function makeLabels (currentMoment, endMoment) {
    const dates = []
    while (currentMoment.isBefore(endMoment, 'day')) {
        currentMoment.add(1, 'days')
        dates.push(currentMoment.format('DD'))
    }

    return dates
}

function objectToCSVRow (dataObject, headers, isHeader = false) {
    const dataArray = []
    for (const o in dataObject) {
        if (!isHeader && !headers.includes(o)) {
            continue
        }

        if (typeof dataObject[o] === 'boolean') {
            dataObject[o] = dataObject[o] === true ? 'Yes' : 'No'
        }

        const innerValue = dataObject[o] === null ? '' : dataObject[o].toString()

        let result = innerValue.replace(/"/g, '""')
        result = '"' + result + '"'
        dataArray.push(result)
    }
    return dataArray.join(',') + '\r\n'
}

const mainChartOpts = {
    tooltips: {
        enabled: false,
        custom: CustomTooltips,
        intersect: true,
        mode: 'index',
        position: 'nearest',
        callbacks: {
            labelColor: function (tooltipItem, chart) {
                return { backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].borderColor }
            }
        }
    },
    maintainAspectRatio: false,
    legend: {
        display: false
    },
    scales: {
        xAxes: [
            {
                gridLines: {
                    drawOnChartArea: false
                }
            }],
        yAxes: [
            {
                ticks: {
                    beginAtZero: true,
                    maxTicksLimit: 5,
                    stepSize: Math.ceil(250 / 5),
                    max: 250
                }
            }]
    },
    elements: {
        point: {
            radius: 0,
            hitRadius: 10,
            hoverRadius: 4,
            hoverBorderWidth: 3
        }
    }
}

class Dashboard extends Component {
    constructor (props) {
        super(props)
        this.getOption = this.getOption.bind(this)
        this.state = {
            sources: [],
            leadCounts: [],
            start_date: null,
            end_date: null,
            totalBudget: 0,
            totalEarnt: 0,
            leadsToday: 0,
            newDeals: 0,
            newCustomers: 0,
            deals: [],
            invoices: [],
            quotes: [],
            payments: [],
            expenses: [],
            tasks: [],
            activeTab: '1'
        }

        this.toggle = this.toggle.bind(this)
        this.getChartData = this.getChartData.bind(this)
        this.doExport = this.doExport.bind(this)
        this.setDates = this.setDates.bind(this)
        this.onRadioBtnClick = this.onRadioBtnClick.bind(this)
        this.fetchData = this.fetchData.bind(this)
    }

    componentDidMount () {
        this.fetchData()

        // window.setInterval(() => {
        //     this.fetchData()
        // }, 5000)
    }

    fetchData () {
        axios.get('/api/dashboard')
            .then((r) => {
                if (r.data) {
                    this.setState(
                        {
                            sources: r.data.sources,
                            leadCounts: r.data.leadCounts,
                            totalBudget: r.data.totalBudget,
                            totalEarnt: r.data.totalEarnt,
                            leadsToday: r.data.leadsToday,
                            newDeals: r.data.newDeals,
                            newCustomers: r.data.newCustomers,
                            invoices: r.data.invoices,
                            quotes: r.data.quotes,
                            payments: r.data.payments,
                            expenses: r.data.expenses,
                            tasks: r.data.tasks
                        }
                    )
                }
            })
            .catch((e) => {
                console.warn(e)
            })
    }

    setDates (date) {
        console.log('date 22', date)
        this.setState(date)
    }

    getArrayToExport (entity, radioSelected) {
        let currentMoment = moment().startOf('month')
        let endMoment = moment().endOf('month')

        if (this.state.start_date !== null) {
            currentMoment = moment(this.state.start_date)
        }

        if (this.state.end_date !== null) {
            endMoment = moment(this.state.end_date)
        }

        const start = currentMoment.format('YYYY-MM-DD')
        const end = endMoment.format('YYYY-MM-DD')

        let array = []

        switch (entity) {
            case 'Tasks':
                switch (radioSelected) {
                    case 'Invoiced':
                        // array = formatData(myData, 1, start, end, 'amount', 'status', false)
                        break
                }
                break

            case 'Invoices':
                switch (radioSelected) {
                    case 'Active':
                        array = formatData(this.state.invoices, 1, start, end, 'amount', 'status', false)
                        break

                    case 'Outstanding':
                        array = formatData(this.state.invoices, 2, start, end, 'amount', 'status', false)
                        break
                }
                break

            case 'Expenses':
                switch (radioSelected) {
                    case 'Pending':
                        array = formatData(this.state.expenses, 1, start, end, 'amount', 'status', false)
                        break

                    case 'Invoiced':
                        array = formatData(this.state.expenses, 1, start, end, 'amount', 'status', false)
                        break

                    case 'Paid':
                        array = formatData(this.state.expenses, 1, start, end, 'amount', 'status', false)
                        break
                }

                break

            case 'Payments':
                switch (radioSelected) {
                    case 'Active':
                        array = formatData(this.state.payments, 1, start, end, 'amount', 'status', false)
                        break
                    case 'Refunded':
                        array = formatData(this.state.payments, 6, start, end, 'amount', 'status', false)
                        break
                }
                break

            case 'Quotes':
                switch (radioSelected) {
                    case 'Active':
                        array = formatData(this.state.quotes, 1, start, end, 'amount', 'status', false)
                        break

                    case 'Approved':
                        array = formatData(this.state.quotes, 4, start, end, 'amount', 'status', false)
                        break

                    case 'Unapproved':
                        array = formatData(this.state.quotes, 2, start, end, 'amount', 'status', false)
                        break
                }
        }

        return array
    }

    getChartData () {
        var now = new Date()
        const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()

        let currentMoment = moment().startOf('month')
        let endMoment = moment().endOf('month')

        if (this.state.start_date !== null) {
            currentMoment = moment(this.state.start_date)
        }

        if (this.state.end_date !== null) {
            endMoment = moment(this.state.end_date)
        }

        const start = currentMoment.format('YYYY-MM-DD')
        const end = endMoment.format('YYYY-MM-DD')
        // const currentMoment = moment('2020-02-03')
        // const endMoment = moment('2020-03-17')
        const dates = makeLabels(currentMoment, endMoment)
        const invoiceActive = formatData(this.state.invoices, 1, start, end, 'total', 'status_id')
        const invoiceOutstanding = formatData(this.state.invoices, 2, start, end, 'total', 'status_id')

        const paymentActive = formatData(this.state.payments, 1, start, end, 'amount', 'status_id')
        const paymentRefunded = formatData(this.state.payments, 6, start, end, 'refunded', 'status_id')
        const paymentCompleted = formatData(this.state.payments, 4, start, end, 'amount', 'status_id')

        const quoteActive = formatData(this.state.quotes, 1, start, end, 'total', 'status_id')
        const quoteApproved = formatData(this.state.quotes, 4, start, end, 'total', 'status_id')
        const quoteUnapproved = formatData(this.state.quotes, 2, start, end, 'total', 'status_id')

        const expenseInvoices = removeNullValues(this.state.invoices, 'expense_id')

        const expenseLogged = formatData(this.state.expenses, 1, start, end, 'amount', 'status_id')
        const expensePending = formatData(this.state.expenses, 1, start, end, 'amount', 'status_id')
        const expenseInvoiced = formatData(expenseInvoices, null, start, end, 'amount', 'status_id')
        const expensePaid = formatData(this.state.expenses, 1, start, end, 'amount', 'status_id')

        const taskInvoices = removeNullValues(this.state.invoices, 'task_id')
        const taskInvoiced = taskInvoices.length ? Object.values(formatData(taskInvoices, null, start, end, 'total', 'status_id')) : []

        /* const taskLogged = Object.values(formatData(this.state.tasks, 1, currentMoment, endMoment, 'total', 'status_id'))

        const taskPaid = Object.values(formatData(this.state.tasks, 3, currentMoment, endMoment, 'total', 'status_id')) */

        return [
            {
                name: 'Invoices',
                labels: dates,
                buttons: {
                    Active: {
                        avg: invoiceActive && Object.keys(invoiceActive).length ? invoiceActive.avg : 0,
                        pct: invoiceActive && Object.keys(invoiceActive).length ? invoiceActive.pct : 0,
                        value: invoiceActive && Object.keys(invoiceActive).length ? invoiceActive.value : 0
                    },
                    Outstanding: {
                        avg: invoiceOutstanding && Object.keys(invoiceOutstanding).length ? invoiceOutstanding.avg : 0,
                        pct: invoiceOutstanding && Object.keys(invoiceOutstanding).length ? invoiceOutstanding.pct : 0,
                        value: invoiceOutstanding && Object.keys(invoiceOutstanding).length ? invoiceOutstanding.value : 0
                    }
                },
                datasets: [
                    {
                        label: 'Active',
                        backgroundColor: hexToRgba(brandInfo, 10),
                        borderColor: brandInfo,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: invoiceActive && Object.keys(invoiceActive).length ? Object.values(invoiceActive.data) : []
                    },
                    {
                        label: 'Outstanding',
                        backgroundColor: 'transparent',
                        borderColor: brandDanger,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: invoiceOutstanding && Object.keys(invoiceOutstanding).length ? Object.values(invoiceOutstanding.data) : []
                    }
                ]
            },
            {
                name: 'Payments',
                labels: dates,
                buttons: {
                    Active: {
                        avg: paymentActive && Object.keys(paymentActive).length ? paymentActive.avg : 0,
                        pct: paymentActive && Object.keys(paymentActive).length ? paymentActive.pct : 0,
                        value: paymentActive && Object.keys(paymentActive).length ? paymentActive.value : 0
                    },
                    Refunded: {
                        avg: paymentRefunded && Object.keys(paymentRefunded).length ? paymentRefunded.avg : 0,
                        pct: paymentRefunded && Object.keys(paymentRefunded).length ? paymentRefunded.pct : 0,
                        value: paymentRefunded && Object.keys(paymentRefunded).length ? paymentRefunded.value : 0
                    },
                    Completed: {
                        avg: paymentCompleted && Object.keys(paymentCompleted).length ? paymentCompleted.avg : 0,
                        pct: paymentCompleted && Object.keys(paymentCompleted).length ? paymentCompleted.pct : 0,
                        value: paymentCompleted && Object.keys(paymentCompleted).length ? paymentCompleted.value : 0
                    }
                },
                datasets: [
                    {
                        label: 'Active',
                        backgroundColor: 'transparent',
                        borderColor: brandInfo,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: paymentActive && Object.keys(paymentActive).length ? Object.values(paymentActive.data) : []
                    },
                    {
                        label: 'Refunded',
                        backgroundColor: 'transparent',
                        borderColor: brandDanger,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: paymentRefunded && Object.keys(paymentRefunded).length ? Object.values(paymentRefunded.data) : []
                    },
                    {
                        label: 'Completed',
                        backgroundColor: 'transparent',
                        borderColor: brandSuccess,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: paymentCompleted && Object.keys(paymentCompleted).length ? Object.values(paymentCompleted.data) : []
                    }
                ]
            },
            {
                name: 'Quotes',
                labels: dates,
                buttons: {
                    Active: {
                        avg: quoteActive && Object.keys(quoteActive).length ? quoteActive.avg : 0,
                        pct: quoteActive && Object.keys(quoteActive).length ? quoteActive.pct : 0,
                        value: quoteActive && Object.keys(quoteActive).length ? quoteActive.value : 0
                    },
                    Approved: {
                        avg: quoteApproved && Object.keys(quoteApproved).length ? quoteActive.avg : 0,
                        pct: quoteApproved && Object.keys(quoteApproved).length ? quoteActive.pct : 0,
                        value: quoteApproved && Object.keys(quoteApproved).length ? quoteActive.value : 0
                    },
                    Unapproved: {
                        avg: quoteUnapproved && Object.keys(quoteUnapproved).length ? quoteActive.avg : 0,
                        pct: quoteUnapproved && Object.keys(quoteUnapproved).length ? quoteActive.pct : 0,
                        value: quoteUnapproved && Object.keys(quoteUnapproved).length ? quoteActive.value : 0
                    }
                },
                datasets: [
                    {
                        label: 'Active',
                        backgroundColor: hexToRgba(brandInfo, 10),
                        borderColor: brandInfo,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: quoteActive && Object.keys(quoteActive).length ? Object.values(quoteActive.data) : []
                    },
                    {
                        label: 'Approved',
                        backgroundColor: 'transparent',
                        borderColor: brandSuccess,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: quoteApproved && Object.keys(quoteApproved).length ? Object.values(quoteApproved.data) : []
                    },
                    {
                        label: 'Unapproved',
                        backgroundColor: 'transparent',
                        borderColor: brandDanger,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: quoteUnapproved && Object.keys(quoteUnapproved).length ? Object.values(quoteUnapproved.data) : []
                    }
                ]
            },
            {
                name: 'Tasks',
                labels: dates,
                buttons: {
                    // Logged: getAverages(taskInvoiced),
                    Invoiced: taskInvoiced && Object.keys(taskInvoiced).length ? getAverages(taskInvoiced) : 0
                    // Paid: getAverages(taskPaid)
                },
                datasets: [
                    // {
                    //     label: 'Logged',
                    //     backgroundColor: hexToRgba(brandInfo, 10),
                    //     borderColor: brandInfo,
                    //     pointHoverBackgroundColor: '#fff',
                    //     borderWidth: 2,
                    //     data: taskLogged
                    // },
                    {
                        label: 'Invoiced',
                        backgroundColor: 'transparent',
                        borderColor: brandWarning,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: taskInvoiced && Object.keys(taskInvoiced).length ? taskInvoiced : []
                    }
                    // {
                    //     label: 'Paid',
                    //     backgroundColor: 'transparent',
                    //     borderColor: brandSuccess,
                    //     pointHoverBackgroundColor: '#fff',
                    //     borderWidth: 2,
                    //     data: taskPaid
                    // }
                ]
            },
            {
                name: 'Expenses',
                labels: dates,
                buttons: {
                    Logged: {
                        avg: expenseLogged && Object.keys(expenseLogged).length ? expenseLogged.avg : 0,
                        pct: expenseLogged && Object.keys(expenseLogged).length ? expenseLogged.pct : 0,
                        value: expenseLogged && Object.keys(expenseLogged).length ? expenseLogged.value : 0
                    },
                    Pending: {
                        avg: expensePending && Object.keys(expensePending).length ? expensePending.avg : 0,
                        pct: expensePending && Object.keys(expensePending).length ? expensePending.pct : 0,
                        value: expensePending && Object.keys(expensePending).length ? expensePending.value : 0
                    },
                    Invoiced: {
                        avg: expenseInvoiced && Object.keys(expenseInvoiced).length ? expenseInvoiced.avg : 0,
                        pct: expenseInvoiced && Object.keys(expenseInvoiced).length ? expenseInvoiced.pct : 0,
                        value: expenseInvoiced && Object.keys(expenseInvoiced).length ? expenseInvoiced.value : 0
                    },
                    Paid: {
                        avg: expenseLogged && Object.keys(expenseLogged).length ? expensePaid.avg : 0,
                        pct: expenseLogged && Object.keys(expenseLogged).length ? expensePaid.pct : 0,
                        value: expenseLogged && Object.keys(expenseLogged).length ? expensePaid.value : 0
                    }
                },
                datasets: [
                    {
                        label: 'Logged',
                        backgroundColor: hexToRgba(brandInfo, 10),
                        borderColor: brandInfo,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: expenseLogged && Object.keys(expenseLogged).length ? Object.values(expenseLogged.data) : []
                    },
                    {
                        label: 'Pending',
                        backgroundColor: 'transparent',
                        borderColor: brandPrimary,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 1,
                        borderDash: [8, 5],
                        data: expensePending && Object.keys(expensePending).length ? Object.values(expensePending.data) : []
                    },
                    {
                        label: 'Invoiced',
                        backgroundColor: 'transparent',
                        borderColor: brandWarning,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: expenseInvoiced && Object.keys(expenseInvoiced).length ? Object.values(expenseInvoiced.data) : []
                    },
                    {
                        label: 'Paid',
                        backgroundColor: 'transparent',
                        borderColor: brandSuccess,
                        pointHoverBackgroundColor: '#fff',
                        borderWidth: 2,
                        data: expensePaid && Object.keys(expensePaid).length ? Object.values(expensePaid.data) : []
                    }
                ]
            }
        ]
    }

    getPieOptions () {
        return {
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b} : {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                data: ['Website', 'Personal Contact', 'Email', 'Other', 'Call']
            },
            series: [
                {
                    name: 'Sources',
                    type: 'pie',
                    radius: '55%',
                    center: ['50%', '60%'],
                    data: this.state.sources,
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        }
    }

    getOption () {
        return {
            backgroundColor: '#1b1b1b',
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c}%'
            },
            legend: {
                orient: 'horizontal',
                x: 'left',
                y: 0,
                data: ['Opened', 'Lost', 'Demo', 'Contacted', 'Won', 'No Show']
            },
            // Add Custom Colors
            color: ['#0FB365', '#1EC481', '#28D094', '#48D7A4', '#94E8CA', '#BFF1DF'],
            // Enable drag recalculate
            calculable: true,
            toolbox: {
                show: true,
                feature: {
                    mark: { show: true },
                    restore: { show: true },
                    saveAsImage: { show: true }
                }
            },
            series: [
                {
                    name: 'Deals',
                    type: 'funnel',
                    funnelAlign: 'left',
                    x: '25%',
                    x2: '25%',
                    y: '17.5%',
                    width: '50%',
                    height: '80%',
                    data: this.state.leadCounts
                }
            ]
        }
    }

    toggle (tab) {
        if (this.state.activeTab !== tab) {
            this.setState({ activeTab: tab })
        }
    }

    doExport () {
        const array = this.getArrayToExport(this.state.entity, this.state.radioSelected)

        if (array[0] && Object.keys(array[0]).length) {
            // const colNames = Object.keys(response.data.data[0]);
            const colNames = Object.keys(array[0])

            let csvContent = 'data:text/csv;charset=utf-8,'
            csvContent += objectToCSVRow(colNames, colNames, true)

            array.forEach((item) => {
                csvContent += objectToCSVRow(item, colNames)
            })

            console.log('csv data', csvContent)

            const encodedUri = encodeURI(csvContent)
            const link = document.createElement('a')
            link.setAttribute('href', encodedUri)
            link.setAttribute('download', `${this.state.entity}-${this.state.radioSelected}.csv`)
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        }
    }

    onRadioBtnClick (radioSelected, entity) {
        this.setState({
            radioSelected: radioSelected,
            entity: entity
        })
    }

    render () {
        const onEvents = {
            click: this.onChartClick,
            legendselectchanged: this.onChartLegendselectchanged
        }

        const charts = this.state.invoices.length ? this.getChartData().map((entry, index) => {
            const buttons = Object.keys(entry.buttons).map((key, value) => {
                return <Button key={value}
                    color="outline-secondary"
                    onClick={() => this.onRadioBtnClick(key, entry.name)}
                    active={this.state.radioSelected === key}>{`${key} £${entry.buttons[key].value}`}</Button>
            })

            const footerButtons = Object.keys(entry.buttons).map((key, value) => {
                return <Col key={value} sm={12} md
                    className="mb-sm-2 mb-0">
                    <div
                        className="text-muted">{key}
                    </div>
                    <strong>Avg {entry.buttons[key].avg}
                        ({entry.buttons[key].pct}%)</strong>
                    <Progress
                        className="progress-xs mt-2"
                        color="warning" value={entry.buttons[key].pct}/>
                </Col>
            })

            return (<Row key={index}>
                <Col>
                    <Card>
                        <CardBody>
                            <Row>
                                <Col sm="5">
                                    <CardTitle
                                        className="mb-0">{entry.name}</CardTitle>
                                    <div
                                        className="small text-muted">November
                                        2015
                                    </div>
                                </Col>
                                <Col sm="7"
                                    className="d-none d-sm-inline-block">
                                    <Button color="primary" onClick={this.doExport()}
                                        className="float-right"><i
                                            className="icon-cloud-download"/></Button>
                                    <ButtonToolbar
                                        className="float-right"
                                        aria-label="Toolbar with button groups">
                                        <ButtonGroup className="mr-3"
                                            aria-label="First group">
                                            {buttons}
                                        </ButtonGroup>
                                    </ButtonToolbar>
                                </Col>
                            </Row>
                            <div className="chart-wrapper"
                                style={{
                                    height: 300 + 'px',
                                    marginTop: 40 + 'px'
                                }}>
                                <Line data={entry}
                                    options={mainChartOpts}
                                    height={300}/>
                            </div>
                        </CardBody>
                        <CardFooter>
                            <Row className="text-center">
                                {footerButtons}
                            </Row>
                        </CardFooter>
                    </Card>
                </Col>
            </Row>)
        }) : null

        let leads = ''

        if (this.state.deals.length) {
            let count = 1

            leads = this.state.deals.map((lead, index) => {
                return (
                    <React.Fragment key={index}>
                        <div key={index} className="media mt-1">
                            <div className="media-left pr-2">
                                <img className="media-object avatar avatar-md rounded-circle"
                                    src={`/files/avatar${count++}.png`} alt="Generic placeholder image"/>
                            </div>
                            <div className="media-body">
                                <p className="text-bold-600 m-0">{lead.title.substring(0, 40)} <span
                                    className="float-right badge badge-success">{lead.status_name}</span></p>
                                <p className="font-small-2 text-muted m-0">{lead.valued_at}<i
                                    className="ft-calendar pl-1"/>{lead.due_date}</p>
                            </div>
                        </div>
                    </React.Fragment>
                )
            })
        }
        return (
            <React.Fragment>
                <Nav tabs>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '1' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('1')
                            }}>
                            Dashboard
                        </NavLink>
                    </NavItem>
                    <NavItem>
                        <NavLink
                            className={this.state.activeTab === '2' ? 'active' : ''}
                            onClick={() => {
                                this.toggle('2')
                            }}>
                            Activity
                        </NavLink>
                    </NavItem>
                </Nav>

                <TabContent activeTab={this.state.activeTab}>
                    <TabPane tabId="1">
                        <MonthPicker onChange={this.setDates}/>

                        <Row>
                            <Col className="col-xl-4" lg={6} md={12}>
                                <CardModule
                                    body={true}
                                    hCenter={true}
                                    header={
                                        <React.Fragment>
                                            <span className="success darken-1">Total Budget</span>
                                            <h3 className="font-large-2 grey darken-1 text-bold-200">{this.state.totalBudget}</h3>
                                        </React.Fragment>
                                    }
                                    content={
                                        <React.Fragment>
                                            <input type="text" value="75"
                                                className="knob hide-value responsive angle-offset"
                                                data-angleOffset="0" data-thickness=".15"
                                                data-linecap="round" data-width="150"
                                                data-height="150" data-inputColor="#e1e1e1"
                                                data-readOnly="true" data-fgColor="#37BC9B"
                                                data-knob-icon="ft-trending-up"/>

                                            <ul className="list-inline clearfix mt-2 mb-0">
                                                <li className="border-right-grey border-right-lighten-2 pr-2">
                                                    <h2 className="grey darken-1 text-bold-400">75%</h2>
                                                    <span className="success">Completed</span>
                                                </li>
                                                <li className="pl-2">
                                                    <h2 className="grey darken-1 text-bold-400">25%</h2>
                                                    <span className="danger">Remaining</span>
                                                </li>
                                            </ul>
                                        </React.Fragment>
                                    }
                                />
                            </Col>
                            <Col className="col-xl-4" lg={6} md={12}>
                                <CardModule
                                    body={false}
                                    content={
                                        <div className="earning-chart position-relative">
                                            <div className="chart-title position-absolute mt-2 ml-2">
                                                <h1 className="font-large-2 grey darken-1 text-bold-200">{this.state.totalEarnt}</h1>
                                                <span className="text-muted">Total Earning</span>
                                            </div>
                                            <div className="chartjs height-400">
                                                <canvas id="earning-chart" className="height-400 block"/>
                                            </div>
                                            <div
                                                className="chart-stats position-absolute position-bottom-0 position-right-0 mb-2 mr-3">
                                                <a href="#" className="btn bg-info mr-1 white">Statistics <i
                                                    className="ft-bar-chart"/></a> <span
                                                    className="text-muted">for the <a
                                                        href="#">last year.</a></span>
                                            </div>
                                        </div>
                                    }
                                />
                            </Col>
                            <Col className="col-xl-4" lg={12} md={12}>
                                <CardModule
                                    body={true}
                                    content={
                                        <div>
                                            <div className="media">
                                                <div className="media-body text-left">
                                                    <h3 className="success">{this.state.leadsToday}</h3>
                                                    <span>Today's Leads</span>
                                                </div>
                                                <div className="media-right media-middle">
                                                    <i className="ft-award success font-large-2 float-right"/>
                                                </div>
                                            </div>

                                            <div className="progress mt-1 mb-0" style={{ height: '7px' }}>
                                                <div className="progress-bar bg-success" role="progressbar"
                                                    style={{ width: '80%' }} aria-valuenow="80" aria-valuemin="0"
                                                    aria-valuemax="100"/>
                                            </div>
                                        </div>
                                    }
                                />

                                <CardModule
                                    body={true}
                                    content={
                                        <div>
                                            <div className="media">
                                                <div className="media-body text-left">
                                                    <h3 className="deep-orange">{this.state.newDeals}</h3>
                                                    <span>New Deal</span>
                                                </div>
                                                <div className="media-right media-middle">
                                                    <i className="ft-package deep-orange font-large-2 float-right"/>
                                                </div>
                                            </div>

                                            <div className="progress mt-1 mb-0" style={{ height: '7px' }}>
                                                <div className="progress-bar bg-deep-orange" role="progressbar"
                                                    style={{ width: '35%' }} aria-valuenow="35" aria-valuemin="0"
                                                    aria-valuemax="100"/>
                                            </div>
                                        </div>
                                    }
                                />

                                <CardModule
                                    body={true}
                                    content={
                                        <div>
                                            <div className="media">
                                                <div className="media-body text-left">
                                                    <h3 className="info">{this.state.newCustomers}</h3>
                                                    <span>New Customers</span>
                                                </div>
                                                <div className="media-right media-middle">
                                                    <i className="ft-users info font-large-2 float-right"/>
                                                </div>
                                            </div>

                                            <div className="progress mt-1 mb-0" style={{ height: '7px' }}>
                                                <div className="progress-bar bg-success" role="progressbar"
                                                    style={{ width: '35%' }} aria-valuenow="35" aria-valuemin="0"
                                                    aria-valuemax="100"/>
                                            </div>
                                        </div>
                                    }
                                />
                            </Col>
                        </Row>

                        <Row className="match-height">
                            <Col className="col-xl-6" lg={12}>
                                <CardModule
                                    body={true}
                                    header={
                                        <React.Fragment>
                                            <h4 className="card-title">Deals Funnel <span
                                                className="text-muted text-bold-400">This Month</span></h4>
                                            <a className="heading-elements-toggle"><i
                                                className="ft-more-horizontal font-medium-3"/></a>
                                            <div className="heading-elements">
                                                <ul className="list-inline mb-0">
                                                    <li><a data-action="reload"><i className="ft-rotate-cw"/></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </React.Fragment>
                                    }
                                    content={
                                        <ReactEcharts option={this.getOption()}/>
                                    }
                                />

                            </Col>
                            <Col className="col-xl-6" lg={12}>
                                <CardModule
                                    cardHeight='410px'
                                    body={true}
                                    header={
                                        <React.Fragment>
                                            <h4 className="card-title">Deals <span className="text-muted text-bold-400">- Won 5</span>
                                            </h4>
                                            <a className="heading-elements-toggle"><i
                                                className="ft-more-horizontal font-medium-3"/></a>
                                            <div className="heading-elements">
                                                <ul className="list-inline mb-0">
                                                    <li><a data-action="reload"><i className="ft-rotate-cw"/></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </React.Fragment>
                                    }
                                    content={
                                        <div style={{
                                            height: '300px',
                                            overflowY: 'auto'
                                        }} id="deals-list-scroll"
                                        className="card-body height-350 position-relative ps-container ps-theme-default"
                                        data-ps-id="6205b797-6d0d-611f-25fd-16195eadda29">
                                            {leads}
                                        </div>
                                    }
                                />
                            </Col>
                        </Row>

                        <Row className="match-height">
                            <Col className="col-xl-8" lg={12}>
                                <StatsCard/>
                            </Col>

                            <Col className="col-xl-4" lg={12}>
                                <CardModule
                                    body={true}
                                    header={
                                        <React.Fragment>
                                            <h4 className="card-title">Sources <span
                                                className="text-muted text-bold-400">This Month</span></h4>
                                            <a className="heading-elements-toggle"><i
                                                className="ft-more-horizontal font-medium-3"/></a>
                                            <div className="heading-elements">
                                                <ul className="list-inline mb-0">
                                                    <li><a data-action="reload"><i className="ft-rotate-cw"/></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </React.Fragment>
                                    }
                                    content={
                                        <ReactEcharts
                                            option={this.getPieOptions()}
                                            style={{ height: 300 }}
                                            onChartReady={this.onChartReady}
                                            onEvents={onEvents}
                                        />
                                    }
                                />
                            </Col>
                        </Row>

                        {charts}

                    </TabPane>
                </TabContent>

                <TabContent activeTab={this.state.activeTab}>
                    <TabPane tabId="2">
                        <MessageContainer/>
                    </TabPane>
                </TabContent>
            </React.Fragment>
        )
    }
}

export default Dashboard
