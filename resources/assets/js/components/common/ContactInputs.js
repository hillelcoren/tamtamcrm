import React from 'react'
import { Col, Row, Button, FormGroup, Label, Input } from 'reactstrap'

const ContactInputs = (props) => {
    return (
        props.contacts.map((val, idx) => {
            return (
                <div key={idx}>
                    <Row form>
                        <Col md={6}>
                            <FormGroup>
                                <Label for="exampleEmail">First Name</Label>
                                <Input type="text"
                                    data-id={idx}
                                    value={props.contacts[idx].first_name}
                                    data-field="first_name"
                                />
                            </FormGroup>
                        </Col>
                        <Col md={6}>
                            <FormGroup>
                                <Label for="examplePassword">Last Name</Label>
                                <Input type="text"
                                    data-id={idx}
                                    value={props.contacts[idx].last_name}
                                    data-field="last_name"
                                />
                            </FormGroup>
                        </Col>
                    </Row>

                    <Row form>
                        <Col md={4}>
                            <FormGroup>
                                <Label for="exampleEmail">Email</Label>
                                <Input type="text"
                                    data-id={idx}
                                    value={props.contacts[idx].email}
                                    data-field="email"
                                />
                            </FormGroup>
                        </Col>
                        <Col md={4}>
                            <FormGroup>
                                <Label for="examplePassword">Phone</Label>
                                <Input type="text"
                                    data-id={idx}
                                    value={props.contacts[idx].phone}
                                    data-field="phone"
                                />
                            </FormGroup>
                        </Col>

                        <Col md={4}>
                            <FormGroup>
                                <Label for="examplePassword">Password</Label>
                                <Input type="password"
                                    data-id={idx}
                                    value={props.contacts[idx].password}
                                    data-field="password"
                                />
                            </FormGroup>
                        </Col>
                    </Row>

                    <Button color="danger" size="lg" block onClick={() => props.removeContact(idx)}>
                        Remove Contact
                    </Button>
                </div>
            )
        })
    )
}
export default ContactInputs
