import React, { Component } from 'react'
import axios from 'axios'
import {
    Button,
    UncontrolledTooltip
} from 'reactstrap'

export default class CsvImporter extends Component {
    constructor (props) {
        super(props)

        this.export = this.export.bind(this)
    }

    objectToCSVRow (dataObject, headers, isHeader = false) {
        const dataArray = []
        for (const o in dataObject) {
            if (!isHeader && !headers.includes(o)) {
                continue
            }

            if (typeof dataObject[o] === 'object') {
                dataObject[o] = ''
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

    export () {
        axios.get(this.props.url)
            .then(response => {
                if (response.data.data && Object.keys(response.data.data).length) {
                    const colNames = this.props.columns && this.props.columns.length ? this.props.columns : Object.keys(response.data.data[0])

                    let csvContent = 'data:text/csv;charset=utf-8,'
                    csvContent += this.objectToCSVRow(colNames, colNames, true)

                    response.data.data.forEach((item) => {
                        csvContent += this.objectToCSVRow(item, colNames)
                    })

                    const encodedUri = encodeURI(csvContent)
                    const link = document.createElement('a')
                    link.setAttribute('href', encodedUri)
                    link.setAttribute('download', this.props.filename)
                    document.body.appendChild(link)
                    link.click()
                    document.body.removeChild(link)
                }
            })
    }

    render () {
        return <React.Fragment>
            <UncontrolledTooltip placement="right"
                target="exportTooltip">
                    Export
            </UncontrolledTooltip>

            <Button id="exportTooltip" onClick={this.export}
                color="primary">Export</Button>
        </React.Fragment>
    }
}
