import React, { Component } from 'react'
import {
    Modal,
    ModalHeader,
    ModalBody,
    ModalFooter,
    Button,
    UncontrolledTooltip
} from 'reactstrap'
import axios from 'axios'
import SuccessMessage from './SucessMessage'
import ErrorMessage from './ErrorMessage'

export default class AboutModal extends Component {
    constructor (props) {
        super(props)
        this.state = {
            modal: false,
            check: false,
            errors: [],
            showSuccessMessage: false,
            showErrorMessage: false,
            message: ''
        }

        this.toggle = this.toggle.bind(this)
        this.sendMessage = this.sendMessage.bind(this)
        this.handleChange = this.handleChange.bind(this)
    }

    toggle () {
        this.setState({
            modal: !this.state.modal,
            errors: []
        })
    }

    getData () {
        axios.get('/api/support/messages/send')
            .then(function (response) {

            })
            .catch(function (error) {
                alert(error)
                console.log(error)
            })
    }

    sendMessage () {
        axios.post('/api/support/messages/send', { message: this.state.message, send_logs: this.state.check })
            .then(function (response) {

            })
            .catch(function (error) {
                alert(error)
                console.log(error)
            })
    }

    handleChange (e) {
        this.setState({
            [e.target.name]: e.target.value
        })
    }

    render () {
        const successMessage = this.state.showSuccessMessage === true
            ? <SuccessMessage message="Your message has been sent successfully"/> : null
        const errorMessage = this.state.showErrorMessage === true ? <ErrorMessage
            message="Your message could not be sent"/> : null

        return (
            <React.Fragment>
                <UncontrolledTooltip placement="right" target="contactTooltip">
                    About
                </UncontrolledTooltip>

                <i id="contactTooltip" onClick={this.toggle}
                    style={{ marginRight: 'auto', color: '#000', fontSize: '26px', cursor: 'pointer' }}
                    className="fa fa-question-circle"/>

                <Modal centered={true} backdrop="static" isOpen={this.state.modal} toggle={this.toggle}
                    className={this.props.className}>
                    <ModalHeader toggle={this.toggle}>Contact us</ModalHeader>
                    <ModalBody>
                        {successMessage}
                        {errorMessage}
                    </ModalBody>
                    <ModalFooter>
                        <Button onClick={this.toggle} color="secondary">Cancel</Button>
                        <Button onClick={this.sendMessage}
                            color="primary">Send</Button>{' '}
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}
