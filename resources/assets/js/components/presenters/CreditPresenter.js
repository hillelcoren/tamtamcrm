import { Badge } from 'reactstrap'
import React from 'react'

export default function CreditPresenter (props) {
    const colors = {
        1: 'secondary',
        2: 'primary',
        3: 'warning',
        4: 'success'
    }

    const statuses = {
        1: 'Draft',
        2: 'Sent',
        3: 'Partial',
        4: 'Applied'
    }

    const { field, entity } = props

    const status = !entity.deleted_at
        ? <Badge color={colors[entity.status_id]}>{statuses[entity.status_id]}</Badge>
        : <Badge className="mr-2" color="warning">Archived</Badge>

    switch (field) {
        case 'status_id':
            return <td onClick={() => props.toggleViewedEntity(entity)} data-label="Status">{status}</td>
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
