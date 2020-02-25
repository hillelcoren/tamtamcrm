import React, { Component } from 'react'
import {
    Card,
    CardText
} from 'reactstrap'

export default class InvoiceTotals extends Component {
    constructor (props) {
        super(props)
    }

    render () {
        return (
            <Card body outline color="danger">
                <CardText className="text-white">
                    <div className="d-flex">
                        <div
                            className="p-2 flex-fill">
                            <h4>Total</h4>
                    £{this.props.entity.total}
                        </div>

                        <div
                            className="p-2 flex-fill">
                            <h4>Balance</h4>
                    £{this.props.entity.balance}
                        </div>
                    </div>
                </CardText>
            </Card>
        )
    }
}
