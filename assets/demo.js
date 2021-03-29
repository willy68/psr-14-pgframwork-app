import React from "react";
import ReactDOM from "react-dom";
import ClientsList from "./components/ClientsList";
import './style/demo.css';

const div = document.getElementById('react');
ReactDOM.render(<ClientsList url="/demo/client" />, div);