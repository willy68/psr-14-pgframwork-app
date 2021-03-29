import React from "react";
import ReactDOM from "react-dom";
import ButtonLike from "./components/ButtonLike";
import './style/app.css';

document.querySelectorAll('span.react-like').forEach(span => {
  const likes = +span.dataset.likes;
  const isLiked = +span.dataset.isLiked === 1;
  ReactDOM.render(<ButtonLike likes = {likes} isLiked = {isLiked}/>, span);
});