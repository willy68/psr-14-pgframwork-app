import React, { useState, useEffect, useCallback, memo } from 'react';
import { findClients } from '../functions/api';
import HighlightRow from './HighlightRow';
import TrSelectable from './TrSelectable';

export default function ClientsList({url}) {
  const [state, setState] = useState({
    clients: null,
    selectedRow: -1
  });

  useEffect(async () => {
    const clients = await findClients();
    setState(s => ({ ...s, clients: clients }));
  }, [])

  const handleSelect = useCallback(
    (index) => {
      setState(s => ({ ...s, selectedRow: index }))
    },
    [],
  );

  const handleFire = useCallback(
    (index) => {
      const location = `${url}/${state.clients[index].id}`;
      window.location.assign(location);
    },
    [state.clients],
  );

  const row = useCallback(selectedRow => {
    return (state.clients ? state.clients.map((client, i) => (
      <RowClient client={client} isActive={selectedRow === i} key={client.code_client} />))
      :
      <></>
    );
  },
    [state.clients],
  );

  return (
    <HighlightRow handleSelect={handleSelect} handleFire={handleFire}>
      <table className="table table-hover table-sm">
        <thead>
          <tr>
            <th>Code</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Code Postal</th>
            <th>Ville</th>
          </tr>
        </thead>
        <tbody>
          {row(state.selectedRow)}
        </tbody>
      </table>
    </HighlightRow>
  );
}

const RowClient = memo(({ client, isActive }) => {
  return (
    <TrSelectable
      isActive={isActive}>
      <td>{client.code_client}</td>
      <td>{client.nom}</td>
      <td>{client.email}</td>
      <td>{client.adresses && client.adresses[0]?.cp} </td>
      <td>{client.adresses && client.adresses[0]?.ville} </td>
    </TrSelectable>
  );
});