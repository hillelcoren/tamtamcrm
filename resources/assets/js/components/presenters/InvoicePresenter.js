import { Badge } from 'reactstrap'
import React from 'react'
import moment from 'moment'

export default function InvoicePresenter (props) {
    const colors = {
        1: 'secondary',
        2: 'primary',
        3: 'success',
        4: 'warning',
        5: 'danger',
        '-1': 'danger',
        '-2': 'danger',
        '-3': 'danger'
    }

    const statuses = {
        1: 'Draft',
        2: 'Sent',
        3: 'Paid',
        4: 'Partial',
        5: 'Cancelled',
        '-1': 'Overdue',
        '-2': 'Unpaid',
        '-3': 'Reversed'
    }

    const { field, entity } = props

    const status = !entity.deleted_at
        ? <Badge color={colors[entity.status_id]}>{statuses[entity.status_id]}</Badge>
        : <Badge className="mr-2" color="warning">Archived</Badge>

    switch (field) {
        case 'date':
        case 'due_date':
            const date = entity[field].length ? moment(entity[field]).format('DD/MMM/YYYY') : ''
            return <td data-label="Date">{date}</td>
        case 'status_id':
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)} data-label="Status">{status}</td>
        case 'customer_id':
            const index = props.customers.findIndex(customer => customer.id === entity[field])
            const customer = props.customers[index]
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)}
                data-label="Customer">{`${customer.first_name} ${customer.last_name}`}</td>

        default:
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)} key={field}
                data-label={field}>{entity[field]}</td>
    }
}
