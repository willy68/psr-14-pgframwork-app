import React from "react";

export default class ButtonLike extends React.Component {

  state = {
    likes: this.props.likes || 0,
    isLiked: this.props.isLiked || false
  };

  handleClick = () => {
    const isLiked = this.state.isLiked;
    const likes = this.state.likes + (isLiked ? -1 : 1);

    this.setState({ likes, isLiked: !isLiked });
  }

  render() {
    return <button className="btn btn-link" onClick={this.handleClick}>
      {this.state.likes} {this.state.isLiked ? "Je n'aime plus!" : "J'aime !"}
    </button>
  }
};