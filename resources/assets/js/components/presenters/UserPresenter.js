import React from 'react'

export default function UserPresenter (props) {
    const { field, entity } = props

    switch (field) {
        default:
            return <td onClick={() => props.toggleViewedEntity(entity, entity.name)} key={field}
                data-label={field}>{entity[field]}</td>
    }
}
