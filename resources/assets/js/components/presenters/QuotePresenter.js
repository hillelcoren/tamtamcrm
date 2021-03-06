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

    const dueDate = moment(entity.due_date).format('YYYY-MM-DD')

    const is_late = moment().isAfter(dueDate)
    const entity_status = is_late === true ? '-1' : entity.status_id

    const status = !entity.deleted_at
        ? <Badge color={colors[entity_status]}>{statuses[entity_status]}</Badge>
        : <Badge className="mr-2" color="warning">Archived</Badge>

    switch (field) {
        case 'date':
        case 'due_date': {
            const date = entity[field].length ? moment(entity[field]).format('DD/MMM/YYYY') : ''
            return <td data-label="Date">{date}</td>
        }

        case 'status_id':
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)} data-label="Status">{status}</td>

        case 'customer_id': {
            const index = props.customers.findIndex(customer => customer.id === entity[field])
            const customer = props.customers[index]
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)}
                data-label="Customer">{customer.name}</td>
        }

        default:
            return <td onClick={() => props.toggleViewedEntity(entity, entity.number)} key={field}
                data-label={field}>{entity[field]}</td>
    }
}
