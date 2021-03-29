import React from 'react';

export default function TrSelectable(props) {
  return (
    <tr className={props.isActive ? 'active' : ''}>
      {props.children}
    </tr>
  );
}