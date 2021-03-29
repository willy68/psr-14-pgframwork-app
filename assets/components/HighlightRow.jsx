import React, { useState, useRef, useEffect, useCallback } from 'react';

export default function HighlightRow({ handleSelect, handleFire, ...props }) {
  let startIndex = 0;

  const [state, setState] = useState({
    selectedRow: -1,
    scrollTop: 0,
    cancelScroll: false
  });

  const el = useRef();

  useEffect(() => {

    if (el && el.current) {
      el.current.addEventListener('keydown', handleKeyboardEvent);

      return function cleanup() {
        el.current.removeEventListener('keydown', handleKeyboardEvent);
      }

    }
  }, [state.selectedRow]);

  const handleKeyboardEvent = useCallback(function (e) {
    if (el && el.current) {
      const tbody = el.current.getElementsByTagName('tbody');
      const trCollection = tbody[0].getElementsByTagName('tr');
      if (trCollection.length === 0) {
        return;
      }
      let index = state.selectedRow;
      switch (e.code) {
        case 'ArrowUp':
          if (index === 0 || index === -1) {
            index = trCollection.length;
          }
          index--;
          e.preventDefault();
          break;
        case 'ArrowDown':
          if (index === trCollection.length - 1) {
            index = -1;
          }
          index++;
          e.preventDefault();
          break;
        case 'Enter':
          if (index !== -1) {
            e.preventDefault();
            handleFire(index);
          }
          break;
        default:
          break;
      }
      if (index !== state.selectedRow) {
        setState({ selectedRow: index, scrollTop: scroll(index), cancelScroll: true });
        handleSelect(index);
      }
    }
  }, [el, state.selectedRow]);

  const handleMouseClick = useCallback(function (e) {
    let index = state.selectedRow;
    e.preventDefault();

    let tr = e.target;
    while (tr && tr.tagName !== 'TR') {
      tr = tr.parentElement;
    }

    if (tr && el && el.current) {
      const tbody = el.current.getElementsByTagName('tbody');
      const trCollection = tbody[0].getElementsByTagName('tr');
      if (trCollection.length === 0) {
        return;
      }

      if (trCollection[0].rowIndex > 0) {
        startIndex = trCollection[0].rowIndex;
      }

      index = tr.rowIndex - startIndex;
      setState({ selectedRow: index, scrollTop: scroll(index), cancelScroll: true });
      handleSelect(index);
    }
  }, [el, state.selectedRow]);

  const handleMouseDblClick = useCallback(function (e) {
    let index = state.selectedRow;
    e.preventDefault();

    let tr = e.target;
    while (tr && tr.tagName !== 'TR') {
      tr = tr.parentElement;
    }

    if (tr && el && el.current) {
      const tbody = el.current.getElementsByTagName('tbody');
      const trCollection = tbody[0].getElementsByTagName('tr');
      if (trCollection.length === 0) {
        return;
      }

      if (trCollection[0].rowIndex > 0) {
        startIndex = trCollection[0].rowIndex;
      }

      index = tr.rowIndex - startIndex;
      setState({ selectedRow: index, scrollTop: scroll(index), cancelScroll: true });
      if (index > -1) {
        handleSelect(index);
        handleFire(index);
      }
    }
  }, [el, state.selectedRow]);

  const handleScroll = useCallback(function (e) {

    if (state.cancelScroll && state.scrollTop !== e.target.scrollTop) {
      e.target.scrollTop = state.scrollTop;
      setState(s => ({ ...s, cancelScroll: false }));
    }
  }, [state.cancelScroll, state.scrollTop]);

  const scroll = function (index) {
    if ((!el && !el.current)) {
      return state.scrollTop;
    }

    const trCollection = el.current.getElementsByTagName('tr');
    if (!trCollection || trCollection.length === 0) {
      return state.scrollTop;
    }

    const thead = el.current.getElementsByTagName('thead');
    if (!thead || thead.length === 0) {
      return state.scrollTop;
    }

    let theadHeight = 0;
    const theadFixed = thead[0];
    if (theadFixed) {
      theadHeight = theadFixed.offsetHeight;
    }

    const rowEl = trCollection[index];
    if (rowEl.offsetTop < el.current.scrollTop + theadHeight) {
      el.current.scrollTop = rowEl.offsetTop - theadHeight;

      return (rowEl.offsetTop - theadHeight);

    } else if ((rowEl.offsetTop + rowEl.offsetHeight + theadHeight) >
      (el.current.scrollTop + el.current.offsetHeight)) {
        el.current.scrollTop = rowEl.offsetTop + rowEl.offsetHeight + theadHeight - el.current.offsetHeight;

      return (rowEl.offsetTop + rowEl.offsetHeight + theadHeight - el.current.offsetHeight);

    }

    return state.scrollTop;
  }

  return (
    <div className="fixed-header"
      ref={el}
      onClick={handleMouseClick}
      onScroll={handleScroll}
      onDoubleClick={handleMouseDblClick}
      tabIndex="0">
      {props.children}
    </div>
  );

}