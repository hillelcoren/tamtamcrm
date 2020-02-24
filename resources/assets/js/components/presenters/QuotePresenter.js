import { Badge } from 'reactstrap'
import React from 'react'
import moment from 'moment'

export default function QuotePresenter (props) {
    const colors = {
        1: 'secondary',
        2: 'primary',
        4: 'success',
        '-1': 'danger'
    }

    const statuses = {
        1: 'Draft',
        2: 'Sent',
        4: 'Approved',
        '-1': 'Expired'
    }

    const { field, entity } = props

    const status = !entity.deleted_at
        ? <Badge color={colors[entity.status_id]}>{statuses[entity.status_id]}</Badge>
        : <Badge color="warning">Archived</Badge>

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
