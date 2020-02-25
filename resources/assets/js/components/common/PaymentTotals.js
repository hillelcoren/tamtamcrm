import React, { Component } from 'react'
import {
    Card,
    CardText
} from 'reactstrap'

export default class PaymentTotals extends Component {
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
                            <h4>Amount</h4>
              £{this.props.entity.amount}
                        </div>

                        <div
                            className="p-2 flex-fill">
                            <h4>Applied</h4>
              £{this.props.entity.applied}
                        </div>

                        <div
                            className="p-2 flex-fill">
                            <h4>Refunded</h4>
                        £{this.props.entity.refunded}
                        </div>
                    </div>
                </CardText>
            </Card>
        )
    }
}
