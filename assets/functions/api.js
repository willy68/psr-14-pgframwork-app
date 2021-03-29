/**
 * @param {RequestInfo} url
 * @param params
 * @return {Promise<Object>}
 */
export async function jsonFetch (url, params = {}) {
  // Si on reçoit un FormData on le convertit en objet
  if (params.body instanceof FormData) {
    params.body = Object.fromEntries(params.body)
  }
  // Si on reçoit un objet on le convertit en chaine JSON
  if (params.body && typeof params.body === 'object') {
    params.body = JSON.stringify(params.body)
  }
  params = {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    ...params
  }

  const response = await fetch(url, params)
  if (response.status === 204) {
    return null
  }
  const data = await response.json()
  if (response.ok) {
    return data
  }
  throw new Error(data, response.status)
}

export async function findClients() {
  return await jsonFetch('/api/clients?include=adresses', { method: 'GET' });
}