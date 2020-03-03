import * as React from 'react'

class MessageDialog extends React.Component {
    constructor (props) {
        super(props)
        this.state = {
            messageText: this.props.message ? this.props.message.comment : ''
        }
        this.onChange = this.onChange.bind(this)
    }

    onChange (e) {
        this.setState({
            messageText: e.target.value
        })
    }

    render () {
        const { mode, submitMessage } = this.props
        const { messageText } = this.state
        return (

            <div className="well">
                <form className="form-horizontal" role="form">
                    <h4>What's New</h4>
                    <div className="form-group p-2">
                        <textarea className="form-control" placeholder="Update your status"
                            onChange={this.onChange} />
                    </div>
                    <button onClick={() => submitMessage(messageText, mode)} className="btn btn-primary pull-right"
                        type="button">Post
                    </button>
                    <ul className="list-inline">
                        <li><a href=""><i className="glyphicon glyphicon-upload" /></a></li>
                        <li><a href=""><i className="glyphicon glyphicon-camera" /></a></li>
                        <li><a href=""><i className="glyphicon glyphicon-map-marker" /></a></li>
                    </ul>
                </form>
            </div>

        )
    }
}

export default MessageDialog
